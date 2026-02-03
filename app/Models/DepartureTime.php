<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class DepartureTime extends Model
{
    /** @use HasFactory<\Database\Factories\DepartureTimeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'departure_time',
        'applicable_days',
        'is_active',
        'display_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'applicable_days' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return MorphMany<RoutineAssignment>
     */
    public function assignments(): MorphMany
    {
        return $this->morphMany(RoutineAssignment::class, 'assignable');
    }

    public function isApplicableToday(): bool
    {
        $days = $this->applicable_days ?? [];

        if ($days === []) {
            return true;
        }

        $today = strtolower(now()->format('D'));

        return in_array($today, $days, true);
    }

    public function getNextOccurrence(): ?CarbonInterface
    {
        if (! $this->is_active) {
            return null;
        }

        $time = $this->departure_time;

        if ($time === null) {
            return null;
        }

        $now = now();
        $candidate = Carbon::parse($now->toDateString().' '.$time);

        if ($this->isApplicableToday() && $candidate->greaterThan($now)) {
            return $candidate;
        }

        $days = $this->applicable_days ?? [];

        for ($offset = 1; $offset <= 7; $offset++) {
            $next = $now->copy()->addDays($offset);
            $dayKey = strtolower($next->format('D'));

            if ($days === [] || in_array($dayKey, $days, true)) {
                return Carbon::parse($next->toDateString().' '.$time);
            }
        }

        return null;
    }

    /**
     * @param  Builder<DepartureTime>  $query
     * @return Builder<DepartureTime>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }
}
