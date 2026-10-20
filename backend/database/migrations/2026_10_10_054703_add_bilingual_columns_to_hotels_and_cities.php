<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('name_en', 191)->nullable()->after('name');
            $table->text('description_en')->nullable()->after('description');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->string('name_en', 191)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'description_en']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('name_en');
        });
    }
};