<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('date')) {
            $date = $request->input('date');
            $end_date = date('Y-m-d', strtotime($date . ' +4 days'));

            $dateRange = [
                date('Y-m-d', strtotime($date . ' +4 days')),
                date('Y-m-d', strtotime($date . ' +3 days')),
                date('Y-m-d', strtotime($date . ' +2 days')),
                date('Y-m-d', strtotime($date . ' +1 days')),
                date('Y-m-d', strtotime($date . ' +0 days')),
            ];
            // dd($dateRange);
        } else {
            $date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime($date . ' +4 days'));

            $dateRange = [
                date('Y-m-d', strtotime($date . ' +4 days')),
                date('Y-m-d', strtotime($date . ' +3 days')),
                date('Y-m-d', strtotime($date . ' +2 days')),
                date('Y-m-d', strtotime($date . ' +1 days')),
                date('Y-m-d', strtotime($date . ' +0 days')),
            ];
        }
        $schedules = Schedule::with(['appointments', 'user'])
            ->where(function ($q) use ($dateRange) {
                $q->whereBetween('start_date', [$dateRange[0], end($dateRange)])
                    ->orWhereBetween('end_date', [$dateRange[0], end($dateRange)])
                    ->orWhere(function ($q2) use ($dateRange) {
                        $q2->where('start_date', '<=', $dateRange[0])
                            ->where('end_date', '>=', end($dateRange));
                    });
            })
            ->paginate(10);
        $doctors = User::where('role', 4)->get(); // Получаем всех пользователей с ролью "Врач" (role = 4)
        // dd($schedules[1]->generateTimeSlots('2025-10-01'));
        return view('admin.appointments.index', compact('schedules', 'doctors', 'date'));
    }

    public function store(Schedule $schedule, Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_iin' => 'required|string|max:12',
            'patient_phone' => 'required|string|max:20',
            'date' => 'required',
            'time' => 'nullable|date_format:H:i',
            'service_id' => 'required|exists:services,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($schedule->hasUnlimitedAppointments()) {
            // Для неограниченных записей время не обязательно
        } else {
            if (empty($validated['time'])) {
                return back()->withErrors(['time' => 'Пожалуйста, выберите время записи.'])->withInput();
            }

            $bookedSlots = $schedule->isTimeSlotAvailable($validated['date'], $validated['time']);

            if (!$bookedSlots) {
                return back()->withErrors(['time' => 'Выбранный временной слот уже занят. Пожалуйста, выберите другой.'])->withInput();
            }

            $appointments = Appointment::create([
                'schedule_id' => $schedule->id,
                'registrar_id' => Auth::user()->id,
                'service_id' => $validated['service_id'],
                'client_name' => $validated['patient_name'],
                'patient_iin' => $validated['patient_iin'],
                'client_phone' => $validated['patient_phone'],
                'appointment_date' => $validated['date'],
                'appointment_time' => $validated['time'],
                'appointment_end_time' => Carbon::parse($validated['time'])->addMinutes($schedule->appointment_interval)->format('H:i'),
                'total_price' => Service::find($validated['service_id'])->price ?? 0,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            if (!$appointments) {
                return back()->withErrors(['general' => 'Ошибка при создании записи. Пожалуйста, попробуйте снова.'])->withInput();
            }

            return back()->with('success', 'Пациент успешно записан на прием.');
        }
    }
    public function updateAppointment(Appointment $appointment, Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_iin' => 'required|string|max:12',
            'patient_phone' => 'required|string|max:20',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'nullable|date_format:H:i',
            'service_id' => 'required|exists:services,id',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:pending,confirmed,canceled,completed',
        ]);


        $appointment->client_name = $validated['patient_name'];
        $appointment->patient_iin = $validated['patient_iin'];
        $appointment->client_phone = $validated['patient_phone'];
        $appointment->appointment_date = $validated['date'];
        $appointment->service_id = $validated['service_id'];
        $appointment->notes = $validated['notes'] ?? null;
        $appointment->total_price = Service::find($validated['service_id'])->price ?? 0;
        $appointment->status = $validated['status'] ?? $appointment->status;

        if (Schedule::find($appointment->schedule_id)->hasUnlimitedAppointments()) {
            // Для неограниченных записей время не обязательно
        } else {
            $appointment->appointment_time = $validated['time'];
            $appointment->appointment_end_time = Carbon::parse($validated['time'])->addMinutes($appointment->schedule->appointment_interval)->format('H:i');
        }

        $appointment->save();

        return back()->with('success', 'Запись успешно обновлена.');
        // return response()->json(['message' => 'Запись успешно обновлена.']);
    }

    public function destroyAppointment(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(['message' => 'Удалено успешно']);
    }

    public function completeAppointment(Appointment $appointment)
    {
        $appointment->status = 'confirmed';
        $appointment->save();

        return response()->json(['message' => 'Запись успешно завершена.']);
    }

    public function getAppointments(Appointment $appointment)
    {
        $appointment->load(['schedule.user', 'service']);
        return response()->json($appointment);
    }
}
