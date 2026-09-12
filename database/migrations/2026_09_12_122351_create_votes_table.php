<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table): void {
            $table->id();
            $table->string('session_id')->index();
            $table->foreignId('winner_photo_id')->constrained('car_photos');
            $table->foreignId('loser_photo_id')->constrained('car_photos');
            $table->foreignId('winner_car_id')->constrained('cars');
            $table->foreignId('loser_car_id')->constrained('cars');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }

};
