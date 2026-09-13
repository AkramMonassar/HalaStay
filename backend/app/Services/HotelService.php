<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HotelService
{
    public function createHotel(User $owner, array $data, array $images = []): Hotel
    {
        return DB::transaction(function () use ($owner, $data, $images) {
            $hotel = Hotel::create([
                'owner_id' => $owner->id,
                'city_id' => $data['city_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'star_rating' => $data['star_rating'],
                'review_score' => 0,
                'status' => 'pending',
                'is_active' => true,
            ]);

            $this->attachImages($hotel, $images);

            return $hotel;
        });
    }

    public function updateHotel(Hotel $hotel, array $data): Hotel
    {
        $hotel->update([
            'city_id' => $data['city_id'] ?? $hotel->city_id,
            'name' => $data['name'] ?? $hotel->name,
            'description' => array_key_exists('description', $data) ? $data['description'] : $hotel->description,
            'address' => array_key_exists('address', $data) ? $data['address'] : $hotel->address,
            'phone' => array_key_exists('phone', $data) ? $data['phone'] : $hotel->phone,
            'email' => array_key_exists('email', $data) ? $data['email'] : $hotel->email,
            'star_rating' => $data['star_rating'] ?? $hotel->star_rating,
        ]);

        return $hotel->refresh();
    }

    public function attachImages(Hotel $hotel, array $images): void
    {
        if (empty($images)) {
            return;
        }

        $hasCover = $hotel->images()->where('is_cover', true)->exists();
        $sortOrder = (int) $hotel->images()->max('sort_order') + 1;

        foreach ($images as $image) {
            HotelImage::create([
                'hotel_id' => $hotel->id,
                'image_path' => $image->store('hotels', 'public'),
                'is_cover' => !$hasCover,
                'sort_order' => $sortOrder++,
            ]);

            $hasCover = true;
        }
    }

    /** اعتماد الفندق من الأدمن (FR-ADMIN-01) */
    public function approveHotel(Hotel $hotel): Hotel
    {
        if ($hotel->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن اعتماد فندق بحالة ' . $hotel->status . '.',
            ]);
        }

        $hotel->update(['status' => 'approved']);

        return $hotel->refresh();
    }

    /** رفض الفندق من الأدمن */
    public function rejectHotel(Hotel $hotel): Hotel
    {
        if ($hotel->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن رفض فندق بحالة ' . $hotel->status . '.',
            ]);
        }

        $hotel->update(['status' => 'rejected']);

        return $hotel->refresh();
    }
}