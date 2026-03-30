<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plate_id')->constrained('plats')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('plate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
