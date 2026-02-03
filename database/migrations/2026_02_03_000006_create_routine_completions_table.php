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
        Schema::create('routine_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_assignment_id')->constrained()->cascadeOnDelete();
            $table->date('completion_date');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(
                ['routine_assignment_id', 'completion_date'],
                'routine_completions_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routine_completions');
    }
};
