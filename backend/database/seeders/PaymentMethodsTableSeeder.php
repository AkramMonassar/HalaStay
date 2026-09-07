<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsTableSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'الدفع عند الوصول',
                'method_key' => 'cash_on_arrival',
                'type' => 'manual',
                'country_id' => null,
                'description' => 'ادفع مباشرة عند الوصول إلى الفندق',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'تحويل بنكي',
                'method_key' => 'bank_transfer',
                'type' => 'manual',
                'country_id' => null,
                'description' => 'التحويل البنكي مع رفع إشعار الدفع',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'محفظة إلكترونية',
                'method_key' => 'e_wallet',
                'type' => 'manual',
                'country_id' => null,
                'description' => 'الدفع عبر محفظة إلكترونية مع رفع إشعار الدفع',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['method_key' => $method['method_key']],
                $method
            );
        }
    }
}