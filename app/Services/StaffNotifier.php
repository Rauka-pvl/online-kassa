<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\StaffNotification;
use App\Models\User;
use Carbon\Carbon;

class StaffNotifier
{
    public const TYPE_CREATED = 'booking_created';
    public const TYPE_CANCELLED = 'booking_cancelled';

    public static function notify(string $type, Appointment $appointment): void
    {
        $appointment->loadMissing(['service', 'schedule.user']);

        $staff = User::query()
            ->whereIn('role', [1, 3])
            ->where('is_active', true)
            ->get();

        if ($staff->isEmpty()) {
            return;
        }

        $date = Carbon::parse($appointment->appointment_date)->format('d.m.Y');
        $time = $appointment->appointment_time
            ? Carbon::parse($appointment->appointment_time)->format('H:i')
            : '';
        $service = $appointment->service?->name ?? 'услуга';

        $title = $type === self::TYPE_CANCELLED
            ? 'Пациент отменил запись'
            : 'Новая онлайн-запись';

        $body = trim("{$appointment->client_name} · {$service} · {$date} {$time}");

        foreach ($staff as $user) {
            StaffNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'appointment_id' => $appointment->id,
                'title' => $title,
                'body' => $body,
            ]);
        }
    }
}
