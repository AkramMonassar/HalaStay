<?php

namespace Database\Seeders;

use App\Models\AccommodationType;
use App\Models\City;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoHotelsSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::updateOrCreate(
            ['email' => 'owner@halastay.com'],
            [
                'name' => 'صاحب فندق تجريبي',
                'password' => Hash::make('Owner@123456'),
                'role' => 'hotel_owner',
                'is_active' => true,
            ]
        );

        $hotels = [
            [
                'city' => 'الرياض',
                'name' => 'فندق الرياض الدولي',
                'star_rating' => 5,
                'review_score' => 9.2,
                'types' => [
                    ['name' => 'غرفة مزدوجة', 'stay_type' => 'room', 'total_units' => 5, 'base_price' => 350, 'max_adults' => 2, 'max_children' => 1],
                    ['name' => 'جناح ملكي', 'stay_type' => 'suite', 'total_units' => 2, 'base_price' => 900, 'max_adults' => 3, 'max_children' => 2],
                ],
            ],
            [
                'city' => 'الرياض',
                'name' => 'شقق النخيل المفروشة',
                'star_rating' => 3,
                'review_score' => 7.8,
                'types' => [
                    ['name' => 'شقة غرفتين', 'stay_type' => 'apartment', 'total_units' => 4, 'base_price' => 250, 'max_adults' => 4, 'max_children' => 2],
                ],
            ],
            [
                'city' => 'جدة',
                'name' => 'فندق جدة كورنيش',
                'star_rating' => 4,
                'review_score' => 8.5,
                'types' => [
                    ['name' => 'غرفة مطلة على البحر', 'stay_type' => 'room', 'total_units' => 3, 'base_price' => 450, 'max_adults' => 2, 'max_children' => 2],
                    ['name' => 'قاعة مناسبات', 'stay_type' => 'hall', 'total_units' => 1, 'base_price' => 2000, 'max_adults' => 50, 'max_children' => 50],
                ],
            ],
        ];

        foreach ($hotels as $hotelData) {
            $city = City::where('name', $hotelData['city'])->firstOrFail();

            $hotel = Hotel::updateOrCreate(
                ['name' => $hotelData['name'], 'city_id' => $city->id],
                [
                    'owner_id' => $owner->id,
                    'description' => 'وصف تجريبي لفندق ' . $hotelData['name'],
                    'address' => 'حي تجريبي، ' . $hotelData['city'],
                    'star_rating' => $hotelData['star_rating'],
                    'review_score' => $hotelData['review_score'],
                    'status' => 'approved',
                    'is_active' => true,
                ]
            );

            foreach ($hotelData['types'] as $type) {
                AccommodationType::updateOrCreate(
                    ['hotel_id' => $hotel->id, 'name' => $type['name']],
                    [
                        'stay_type' => $type['stay_type'],
                        'total_units' => $type['total_units'],
                        'base_price' => $type['base_price'],
                        'max_adults' => $type['max_adults'],
                        'max_children' => $type['max_children'],
                        'currency_code' => 'SAR',
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}