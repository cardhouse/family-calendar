<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    /** @use HasFactory<\Database\Factories\ChildFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'avatar_color',
        'display_order',
    ];

    /**
     * @return HasMany<RoutineAssignment>
     */
    public function routineAssignments(): HasMany
    {
        return $this->hasMany(RoutineAssignment::class);
    }

    /**
     * @return HasMany<RoutineAssignment>
     */
    public function dailyAssignments(): HasMany
    {
        return $this->routineAssignments()
            ->whereNull('assignable_type')
            ->whereNull('assignable_id');
    }

    /**
     * @return HasMany<RoutineAssignment>
     */
    public function dailyRoutineAssignments(): HasMany
    {
        return $this->dailyAssignments();
    }

    /**
     * @param  Builder<Child>  $query
     * @return Builder<Child>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }
}
