<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table): void {
            $table->id();
            $table->string('source_auction_item_id')->unique();
            $table->string('auction_id')->nullable();
            $table->string('make');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('odometer')->nullable();
            $table->string('units')->nullable();
            $table->string('engine')->nullable();
            $table->string('transmission')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();

            $table->index(['make', 'model', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }

};
