<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitiesTableSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('code', 'SA')->firstOrFail();

        $cities = [
            'الرياض', 'جدة', 'مكة', 'المدينة المنورة',
            'الدمام', 'الخبر', 'أبها', 'جازان',
            'نجران', 'تبوك', 'العلا', 'الطائف',
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['country_id' => $country->id, 'name' => $city],
                ['is_active' => true]
            );
        }
    }
}