<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodation_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->string('name', 120);
            $table->enum('stay_type', ['room', 'apartment', 'suite', 'hall']);
            $table->text('description')->nullable();
            $table->unsignedInteger('max_adults')->default(2);
            $table->unsignedInteger('max_children')->default(2);
            $table->unsignedInteger('total_units')->default(1);
            $table->decimal('base_price', 10, 2)->default(0);
            $table->string('currency_code', 10)->default('SAR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['hotel_id', 'stay_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_types');
    }
};