<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::now()->addDays(fake()->numberBetween(0, 10))->setTime(
            fake()->numberBetween(7, 18),
            fake()->randomElement([0, 15, 30, 45])
        );

        return [
            'name' => fake()->sentence(2),
            'starts_at' => $startsAt,
            'departure_time' => fake()->boolean(60) ? fake()->time('H:i:s') : null,
            'category' => fake()->randomElement(['school', 'sports', 'family', 'other']),
            'color' => fake()->safeHexColor(),
        ];
    }
}
