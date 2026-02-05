<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AdminTimezoneDateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineCompletion extends Model
{
    /** @use HasFactory<\Database\Factories\RoutineCompletionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'routine_assignment_id',
        'completion_date',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completion_date' => 'date',
            'completed_at' => AdminTimezoneDateTime::class,
        ];
    }

    /**
     * @return BelongsTo<RoutineAssignment, self>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(RoutineAssignment::class, 'routine_assignment_id');
    }
}
