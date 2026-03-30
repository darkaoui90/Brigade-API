<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plats', function (Blueprint $table) {
            $table->string('image')->nullable()->after('price');
            $table->boolean('is_available')->default(true)->after('image')->index();
        });
    }

    public function down(): void
    {
        Schema::table('plats', function (Blueprint $table) {
            $table->dropIndex(['is_available']);
            $table->dropColumn(['image', 'is_available']);
        });
    }
};

