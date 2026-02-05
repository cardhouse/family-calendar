<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Setting;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AdminTimezoneDateTime implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?CarbonInterface
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value, 'UTC')->setTimezone($this->timezone());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        return Carbon::parse($value, $this->timezone())
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
    }

    private function timezone(): string
    {
        $timezone = Setting::get('timezone', config('app.timezone'));

        return is_string($timezone) && $timezone !== '' ? $timezone : config('app.timezone');
    }
}
