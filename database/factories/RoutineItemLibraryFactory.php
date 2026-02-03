<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RoutineItemLibrary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutineItemLibrary>
 */
class RoutineItemLibraryFactory extends Factory
{
    protected $model = RoutineItemLibrary::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'display_order' => fake()->numberBetween(1, 50),
        ];
    }
}
