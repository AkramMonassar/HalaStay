<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

class ExpireUnpaidBookings extends Command
{
    protected $signature = 'bookings:expire-unpaid';

    protected $description = 'إنهاء صلاحية الحجوزات غير المدفوعة التي تجاوزت مهلة الدفع';

    public function handle(): int
    {
        $hours = (int) config('halastay.expiry_hours', 24);
        $deadline = now()->subHours($hours);

        $bookings = Booking::where('booking_status', 'pending_payment')
            ->where('created_at', '<', $deadline)
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('لا حجوزات منتهية الصلاحية — الطابور نظيف.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($bookings) {
            foreach ($bookings as $booking) {
                $booking->update(['booking_status' => 'expired']);

                DB::table('booking_status_history')->insert([
                    'booking_id' => $booking->id,
                    'changed_by' => $booking->user_id,
                    'old_status' => 'pending_payment',
                    'new_status' => 'expired',
                    'note' => 'انتهت مهلة الدفع تلقائياً.',
                    'created_at' => now(),
                ]);

                Notification::create([
                    'user_id' => $booking->user_id,
                    'type' => 'booking_expired',
                    'title' => 'انتهت مهلة حجزك',
                    'body' => "الحجز {$booking->booking_number} انتهى لعدم إتمام الدفع خلال المهلة.",
                ]);
            }
        });

        $this->info('تم إنهاء صلاحية ' . $bookings->count() . ' حجزاً.');

        return self::SUCCESS;
    }

}
Schedule::command('bookings:expire-unpaid')->hourly();