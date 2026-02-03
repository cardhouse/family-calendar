<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Child;
use App\Models\RoutineAssignment;
use App\Models\RoutineItemLibrary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutineAssignment>
 */
class RoutineAssignmentFactory extends Factory
{
    protected $model = RoutineAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'routine_item_id' => RoutineItemLibrary::factory(),
            'child_id' => Child::factory(),
            'assignable_type' => null,
            'assignable_id' => null,
            'display_order' => fake()->numberBetween(1, 20),
        ];
    }
}
