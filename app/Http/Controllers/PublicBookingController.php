<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;

class PublicBookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'nullable|date_format:H:i',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'patient_iin' => 'nullable|string|max:12',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $schedule = Schedule::with('appointments')->findOrFail($validated['schedule_id']);

        if (!$schedule->hasUnlimitedAppointments()) {
            if (empty($validated['time'])) {
                return back()->withErrors(['time' => 'Выберите время приёма'])->withInput();
            }

            // Проверка занятости слота (переиспользуем метод, если есть)
            if (method_exists($schedule, 'isTimeSlotAvailable')) {
                if (!$schedule->isTimeSlotAvailable($validated['date'], $validated['time'])) {
                    return back()->withErrors(['time' => 'Слот уже занят, выберите другое время'])->withInput();
                }
            }
        }

        $appointment = Appointment::create([
            'schedule_id' => $schedule->id,
            'service_id' => $service->id,
            'client_name' => $validated['client_name'],
            'patient_iin' => $validated['patient_iin'] ?? null,
            'client_phone' => $validated['client_phone'],
            'appointment_date' => $validated['date'],
            'appointment_time' => $validated['time'] ?? null,
            'appointment_end_time' => isset($validated['time']) && $validated['time']
                ? Carbon::parse($validated['time'])->addMinutes($schedule->appointment_interval)->format('H:i')
                : null,
            'total_price' => $service->price ?? 0,
            'status' => 'pending',
        ]);

        return redirect()->route('booking.confirm')->with('appointment_id', $appointment->id);
    }

    public function confirm(Request $request)
    {
        $appointmentId = session('appointment_id');
        $appointment = $appointmentId ? Appointment::with(['schedule.user', 'service'])->find($appointmentId) : null;
        return view('client.confirm', compact('appointment'));
    }
}


