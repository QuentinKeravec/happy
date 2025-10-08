<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habit_periods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $t->date('started_at');
            $t->date('ended_at')->nullable(); // NULL = en cours
            $t->timestamps();
            $t->index(['habit_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habit_periods');
    }
};
