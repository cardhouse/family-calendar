<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RoutineAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\RoutineAssignmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'routine_item_id',
        'child_id',
        'assignable_type',
        'assignable_id',
        'display_order',
    ];

    /**
     * @return BelongsTo<RoutineItemLibrary, self>
     */
    public function routineItem(): BelongsTo
    {
        return $this->belongsTo(RoutineItemLibrary::class, 'routine_item_id');
    }

    /**
     * @return BelongsTo<Child, self>
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /**
     * @return MorphTo<Model, self>
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<RoutineCompletion>
     */
    public function completions(): HasMany
    {
        return $this->hasMany(RoutineCompletion::class, 'routine_assignment_id');
    }

    /**
     * @return HasOne<RoutineCompletion>
     */
    public function todayCompletion(): HasOne
    {
        return $this->hasOne(RoutineCompletion::class, 'routine_assignment_id')
            ->whereDate('completion_date', now()->toDateString());
    }

    /**
     * @param  Builder<RoutineAssignment>  $query
     * @return Builder<RoutineAssignment>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }
}
