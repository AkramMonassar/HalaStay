<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // code رمز ISO (مثل SA) — NOT NULL + فريد + بلا default.
        // نجعله nullable ليُضاف البلد الآن بلا رمز، ويملؤه الأدمن لاحقًا.
        // (نستخدم SQL خام بدل change() حفاظًا على قيد UNIQUE والنوع varchar(10))
        DB::statement('ALTER TABLE countries MODIFY code VARCHAR(10) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE countries MODIFY code VARCHAR(10) NOT NULL');
    }
};