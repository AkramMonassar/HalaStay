<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payment_methods', 'requires_receipt')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->boolean('requires_receipt')->default(true);
            });
        }

        DB::table('payment_methods')
            ->where('name', 'like', '%عند الوصول%')
            ->update(['requires_receipt' => false]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_methods', 'requires_receipt')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropColumn('requires_receipt');
            });
        }
    }
};
