<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineItemLibrary extends Model
{
    /** @use HasFactory<\Database\Factories\RoutineItemLibraryFactory> */
    use HasFactory;

    protected $table = 'routine_item_library';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_order',
    ];

    /**
     * @return HasMany<RoutineAssignment>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(RoutineAssignment::class, 'routine_item_id');
    }
}
