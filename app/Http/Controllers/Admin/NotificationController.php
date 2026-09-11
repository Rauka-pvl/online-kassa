<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function poll(Request $request)
    {
        $user = $request->user();
        $afterId = (int) $request->query('after_id', 0);

        $base = StaffNotification::where('user_id', $user->id);

        $unreadCount = (clone $base)->whereNull('read_at')->count();
        $latestId = (clone $base)->max('id') ?? 0;

        $notifications = (clone $base)
            ->with('appointment')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(20)
            ->get()
            ->map(fn (StaffNotification $notification) => $this->serialize($notification));

        $unread = StaffNotification::where('user_id', $user->id)
            ->with('appointment')
            ->whereNull('read_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (StaffNotification $notification) => $this->serialize($notification));

        return response()->json([
            'notifications' => $notifications,
            'unread' => $unread,
            'unread_count' => $unreadCount,
            'latest_id' => $latestId,
        ]);
    }

    public function markRead(Request $request, StaffNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        StaffNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    private function serialize(StaffNotification $notification): array
    {
        $date = $notification->appointment?->appointment_date;

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'appointment_id' => $notification->appointment_id,
            'appointment_date' => $date ? \Carbon\Carbon::parse($date)->format('Y-m-d') : null,
            'created_at' => optional($notification->created_at)->toIso8601String(),
        ];
    }
}
