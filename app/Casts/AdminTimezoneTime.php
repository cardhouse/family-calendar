<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Setting;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AdminTimezoneTime implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $timezone = $this->timezone();
        $reference = Carbon::now($timezone)->startOfDay();
        $utc = Carbon::parse($reference->toDateString().' '.$value, 'UTC');

        return $utc->setTimezone($timezone)->format('H:i:s');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timezone = $this->timezone();

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)
                ->setTimezone('UTC')
                ->format('H:i:s');
        }

        $reference = Carbon::now($timezone)->startOfDay();
        $local = Carbon::parse($reference->toDateString().' '.$value, $timezone);

        return $local->setTimezone('UTC')->format('H:i:s');
    }

    private function timezone(): string
    {
        $timezone = Setting::get('timezone', config('app.timezone'));

        return is_string($timezone) && $timezone !== '' ? $timezone : config('app.timezone');
    }
}
