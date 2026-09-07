<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->foreignId('accommodation_type_id')->constrained('accommodation_types')->onDelete('cascade');
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('nights')->default(1);
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('rooms_count')->default(1);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('currency_code', 10)->default('SAR');
            $table->enum('booking_status', [
                'pending_payment',
                'pending_confirmation',
                'confirmed',
                'cancelled',
                'completed',
                'expired',
            ])->default('pending_payment');
            $table->enum('payment_status', [
                'unpaid',
                'pending',
                'under_review',
                'paid',
                'failed',
                'refunded',
            ])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['check_in', 'check_out']);
            $table->index(['booking_status', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};