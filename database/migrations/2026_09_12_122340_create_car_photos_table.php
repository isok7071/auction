<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->string('source_filename')->unique();
            $table->string('storage_path');
            $table->char('checksum', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_photos');
    }
};
