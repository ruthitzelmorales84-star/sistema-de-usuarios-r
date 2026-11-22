<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tic_tac_toe_moves');

        Schema::create('tic_tac_toe_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('tic_tac_toe_games')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('symbol', 1);
            $table->unsignedTinyInteger('position');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tic_tac_toe_moves');
    }
};