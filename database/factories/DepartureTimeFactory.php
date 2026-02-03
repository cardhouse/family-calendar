<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DepartureTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DepartureTime>
 */
class DepartureTimeFactory extends Factory
{
    protected $model = DepartureTime::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $days = ['mon', 'tue', 'wed', 'thu', 'fri'];

        return [
            'name' => fake()->randomElement(['School', 'Practice', 'Camp']),
            'departure_time' => fake()->time('H:i:s'),
            'applicable_days' => fake()->randomElements($days, fake()->numberBetween(2, 5)),
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 20),
        ];
    }
}
