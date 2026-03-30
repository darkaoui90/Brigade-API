<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->string('status')->default('processing')->after('plate_id')->index();
            $table->string('label')->nullable()->after('score');
            $table->text('warning_message')->nullable()->after('label');

            $table->unique(['user_id', 'plate_id']);
        });
    }

    public function down(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'plate_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'label', 'warning_message']);
        });
    }
};

