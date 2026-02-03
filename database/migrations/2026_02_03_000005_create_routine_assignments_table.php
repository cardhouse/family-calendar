<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('routine_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_item_id')->constrained('routine_item_library')->cascadeOnDelete();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('assignable');
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->timestamps();

            $table->unique([
                'routine_item_id',
                'child_id',
                'assignable_type',
                'assignable_id',
            ], 'routine_assignments_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routine_assignments');
    }
};
