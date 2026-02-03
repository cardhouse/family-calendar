<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RoutineAssignment;
use App\Models\RoutineCompletion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoutineCompletion>
 */
class RoutineCompletionFactory extends Factory
{
    protected $model = RoutineCompletion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'routine_assignment_id' => RoutineAssignment::factory(),
            'completion_date' => now()->toDateString(),
            'completed_at' => now(),
        ];
    }
}
