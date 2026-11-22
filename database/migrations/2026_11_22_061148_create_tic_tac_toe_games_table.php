<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tic_tac_toe_moves');
        Schema::dropIfExists('tic_tac_toe_games');

        Schema::create('tic_tac_toe_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_x_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('player_o_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_turn_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('waiting');
            $table->json('board')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tic_tac_toe_games');
    }
};