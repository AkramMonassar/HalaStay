<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 30)->unique();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->string('currency_code', 10)->default('SAR');
            $table->string('payment_gateway', 50)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('gateway_reference', 100)->nullable();
            $table->enum('payment_status', [
                'pending',
                'success',
                'failed',
                'under_review',
                'refunded',
            ])->default('pending');
            $table->string('receipt_image', 255)->nullable();
            $table->text('admin_note')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'payment_status']);
            $table->index(['payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};