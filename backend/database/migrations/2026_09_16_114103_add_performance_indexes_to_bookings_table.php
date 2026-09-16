<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['accommodation_type_id', 'booking_status'], 'bookings_type_status_index');
            $table->index(['check_in', 'check_out'], 'bookings_dates_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_type_status_index');
            $table->dropIndex('bookings_dates_index');
        });
    }
};