<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AdminTimezoneDateTime;
use App\Casts\AdminTimezoneTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CalendarEvent extends Model
{
    /** @use HasFactory<\Database\Factories\CalendarEventFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'starts_at',
        'departure_time',
        'category',
        'color',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => AdminTimezoneDateTime::class,
            'departure_time' => AdminTimezoneTime::class,
        ];
    }

    /**
     * @return MorphMany<RoutineAssignment>
     */
    public function assignments(): MorphMany
    {
        return $this->morphMany(RoutineAssignment::class, 'assignable');
    }

    /**
     * @param  Builder<CalendarEvent>  $query
     * @return Builder<CalendarEvent>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>', now('UTC'))->orderBy('starts_at');
    }

    /**
     * @param  Builder<CalendarEvent>  $query
     * @return Builder<CalendarEvent>
     */
    public function scopeWithDepartureTime(Builder $query): Builder
    {
        return $query->whereNotNull('departure_time');
    }
}
