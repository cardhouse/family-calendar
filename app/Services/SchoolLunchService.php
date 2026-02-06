<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SchoolLunchService
{
    private const OPEN_ENDPOINT = 'https://www.schoolnutritionandfitness.com/webmenus2/api/menuController.php/open';

    private const GRAPHQL_ENDPOINT = 'https://api.schoolnutritionandfitness.com/graphql';

    private const GRAPHQL_QUERY = <<<'GRAPHQL'
query($menu_type_id: String!, $start_date: String!, $end_date: String!) {
  menuType(id: $menu_type_id) {
    id
    name
    items(start_date: $start_date, end_date: $end_date) {
      date
      product {
        name
        hide_on_calendars
        hide_on_web_menu_view
        is_ancillary
        trash
      }
    }
  }
}
GRAPHQL;

    /**
     * @return array{date: string, date_label: string, menu_name: string, items: array<int, string>}|null
     */
    public function forDate(CarbonInterface $date): ?array
    {
        $source = $this->source();

        if ($source === null || ! $this->isSchoolDay($date)) {
            return null;
        }

        $dateInTimezone = Carbon::instance($date)->timezone($this->adminTimezone())->startOfDay();
        $cacheKey = sprintf('school-lunch:%s:%s', $source['menu_id'], $dateInTimezone->toDateString());
        $cacheTtlMinutes = (int) config('services.school_lunch.cache_ttl_minutes', 30);
        $cacheExpiry = now()->addMinutes(max($cacheTtlMinutes, 1));

        /** @var array{date: string, date_label: string, menu_name: string, items: array<int, string>}|null $lunch */
        $lunch = Cache::remember($cacheKey, $cacheExpiry, function () use ($dateInTimezone, $source): ?array {
            return $this->fetchLunch($source, $dateInTimezone);
        });

        return $lunch;
    }

    /**
     * @param  array{menu_id: string, site_code: string|null}  $source
     * @return array{date: string, date_label: string, menu_name: string, items: array<int, string>}|null
     */
    private function fetchLunch(array $source, CarbonInterface $date): ?array
    {
        $menuDocument = $this->menuDocument($source['menu_id']);

        if ($menuDocument === null || ! $this->menuMatchesSiteCode($menuDocument, $source['site_code'])) {
            return null;
        }

        $menuTypeId = data_get($menuDocument, 'menuType.id');

        if (! is_string($menuTypeId) || $menuTypeId === '') {
            return null;
        }

        $formattedDate = $date->format('m/d/Y');
        $response = $this->httpClient()->post(self::GRAPHQL_ENDPOINT, [
            'query' => self::GRAPHQL_QUERY,
            'variables' => [
                'menu_type_id' => $menuTypeId,
                'start_date' => $formattedDate,
                'end_date' => $formattedDate,
            ],
        ]);

        if (! $response->successful()) {
            return null;
        }

        $items = collect($response->json('data.menuType.items', []))
            ->pluck('product')
            ->filter(function (mixed $product): bool {
                return is_array($product) && ! $this->isHiddenProduct($product);
            })
            ->pluck('name')
            ->filter(function (mixed $name): bool {
                return is_string($name) && trim($name) !== '';
            })
            ->map(function (string $name): string {
                return trim($name);
            })
            ->unique()
            ->values();

        if ($items->isEmpty()) {
            return null;
        }

        $menuName = data_get($response->json(), 'data.menuType.name');

        if (! is_string($menuName) || $menuName === '') {
            $menuName = is_string(data_get($menuDocument, 'menuType.name')) ? data_get($menuDocument, 'menuType.name') : 'School menu';
        }

        return [
            'date' => $date->toDateString(),
            'date_label' => $date->format('D M j'),
            'menu_name' => $menuName,
            'items' => $items->all(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function menuDocument(string $menuId): ?array
    {
        $response = $this->httpClient()->get(self::OPEN_ENDPOINT, [
            'id' => $menuId,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : null;
    }

    private function isHiddenProduct(array $product): bool
    {
        return $this->isTruthy(data_get($product, 'trash'))
            || $this->isTruthy(data_get($product, 'is_ancillary'))
            || $this->isTruthy(data_get($product, 'hide_on_calendars'))
            || $this->isTruthy(data_get($product, 'hide_on_web_menu_view'));
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes'], true);
        }

        return false;
    }

    private function isSchoolDay(CarbonInterface $date): bool
    {
        $day = (int) Carbon::instance($date)->timezone($this->adminTimezone())->format('N');

        return $day <= 5;
    }

    /**
     * @return array{menu_id: string, site_code: string|null}|null
     */
    private function source(): ?array
    {
        $sourceUrl = $this->stringConfig('services.school_lunch.source_url');
        $parsedSource = $this->parseSourceUrl($sourceUrl);
        $configuredMenuId = $this->stringConfig('services.school_lunch.menu_id');
        $configuredSiteCode = $this->stringConfig('services.school_lunch.site_code');
        $menuId = $configuredMenuId ?? $parsedSource['menu_id'];

        if ($menuId === null || $menuId === '') {
            return null;
        }

        return [
            'menu_id' => $menuId,
            'site_code' => $configuredSiteCode ?? $parsedSource['site_code'],
        ];
    }

    /**
     * @return array{menu_id: string|null, site_code: string|null}
     */
    private function parseSourceUrl(?string $sourceUrl): array
    {
        if ($sourceUrl === null || $sourceUrl === '') {
            return ['menu_id' => null, 'site_code' => null];
        }

        $query = parse_url($sourceUrl, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            $fragment = parse_url($sourceUrl, PHP_URL_FRAGMENT);

            if (is_string($fragment) && str_contains($fragment, '?')) {
                $query = explode('?', $fragment, 2)[1];
            }
        }

        if (! is_string($query) || $query === '') {
            return [
                'menu_id' => null,
                'site_code' => null,
            ];
        }

        parse_str($query, $params);

        $menuId = is_string($params['id'] ?? null) && $params['id'] !== '' ? $params['id'] : null;
        $siteCode = is_string($params['siteCode'] ?? null) && $params['siteCode'] !== '' ? $params['siteCode'] : null;

        return [
            'menu_id' => $menuId,
            'site_code' => $siteCode,
        ];
    }

    private function menuMatchesSiteCode(array $menuDocument, ?string $siteCode): bool
    {
        if ($siteCode === null || $siteCode === '') {
            return true;
        }

        $availableSiteCodes = data_get($menuDocument, 'site_codes', []);

        if (! is_array($availableSiteCodes) || $availableSiteCodes === []) {
            return true;
        }

        return collect($availableSiteCodes)
            ->map(function (mixed $value): ?string {
                if (is_int($value) || is_string($value)) {
                    return (string) $value;
                }

                return null;
            })
            ->filter()
            ->contains($siteCode);
    }

    private function stringConfig(string $key): ?string
    {
        $value = config($key);

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function httpClient(): PendingRequest
    {
        $timeout = (float) config('services.school_lunch.request_timeout', 10);

        if ($timeout <= 0) {
            $timeout = 10;
        }

        return Http::acceptJson()
            ->timeout($timeout)
            ->retry(2, 300);
    }

    private function adminTimezone(): string
    {
        $timezone = Setting::get('timezone', config('app.timezone'));

        return is_string($timezone) && $timezone !== '' ? $timezone : config('app.timezone');
    }
}
