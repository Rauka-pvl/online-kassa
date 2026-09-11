<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\Service;
use App\Services\StaffNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicBookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time' => 'nullable|date_format:H:i',
            'client_name' => 'required|string|max:255',
            'client_phone' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (!Appointment::isCompletePhone($value)) {
                        $fail('Введите номер полностью, например +7 (701) 111-22-33');
                    }
                },
            ],
            'patient_iin' => 'nullable|regex:/^\d{12}$/',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        $appointment = DB::transaction(function () use ($validated, $service) {
            $schedule = Schedule::lockForUpdate()->findOrFail($validated['schedule_id']);

            Appointment::where('schedule_id', $schedule->id)
                ->whereDate('appointment_date', $validated['date'])
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate()
                ->get();

            if ($schedule->hasUnlimitedAppointments()) {
                if (empty($validated['time'])) {
                    throw ValidationException::withMessages([
                        'time' => 'Укажите время приёма',
                    ]);
                }
            } else {
                if (empty($validated['time'])) {
                    throw ValidationException::withMessages([
                        'time' => 'Выберите время приёма',
                    ]);
                }

                if (!$schedule->isTimeSlotAvailable($validated['date'], $validated['time'])) {
                    throw ValidationException::withMessages([
                        'time' => 'Слот уже занят, выберите другое время',
                    ]);
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
                'appointment_end_time' => isset($validated['time']) && $validated['time'] && $schedule->appointment_interval
                    ? Carbon::parse($validated['time'])->addMinutes($schedule->appointment_interval)->format('H:i')
                    : null,
                'total_price' => $service->price ?? 0,
                'status' => 'pending',
            ]);

            StaffNotifier::notify(StaffNotifier::TYPE_CREATED, $appointment);

            return $appointment;
        });

        return redirect()
            ->route('booking.show', $appointment->manage_token)
            ->with('just_created', true);
    }

    public function show(string $token)
    {
        $appointment = Appointment::with(['schedule.user', 'service'])
            ->where('manage_token', $token)
            ->firstOrFail();

        return view('client.manage', compact('appointment'));
    }

    public function cancel(Request $request, string $token)
    {
        $appointment = Appointment::with(['schedule.user', 'service'])
            ->where('manage_token', $token)
            ->firstOrFail();

        if (!$appointment->canBeCancelledByPatient()) {
            $message = $appointment->status === 'cancelled'
                ? 'Эта запись уже отменена.'
                : 'Отменить запись уже нельзя: приём начался или запись завершена.';

            return redirect()
                ->route('booking.show', $appointment->manage_token)
                ->with('error', $message);
        }

        $appointment->cancelByPatient();
        StaffNotifier::notify(StaffNotifier::TYPE_CANCELLED, $appointment);

        return redirect()
            ->route('booking.show', $appointment->manage_token)
            ->with('success', 'Запись отменена. Слот снова свободен.');
    }

    public function findForm()
    {
        return view('client.find');
    }

    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'client_phone' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (!Appointment::isCompletePhone($value)) {
                        $fail('Введите номер полностью, например +7 (701) 111-22-33');
                    }
                },
            ],
            'code' => 'required|string|max:32',
        ]);

        $id = (int) preg_replace('/\D+/', '', $validated['code']);
        $phoneDigits = Appointment::normalizePhone($validated['client_phone']);

        $appointment = $id ? Appointment::find($id) : null;

        if (
            !$appointment
            || Appointment::normalizePhone($appointment->client_phone) !== $phoneDigits
            || !$appointment->manage_token
        ) {
            return back()
                ->withErrors(['code' => 'Запись не найдена. Проверьте телефон и код ASK-…'])
                ->withInput();
        }

        return redirect()->route('booking.show', $appointment->manage_token);
    }

    public function slots(Schedule $schedule, Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today'
        ]);

        $date = Carbon::parse($request->query('date'))->format('Y-m-d');

        if (Carbon::parse($date)->lt(Carbon::today())) {
            return response()->json([
                'unlimited' => $schedule->hasUnlimitedAppointments(),
                'working' => false,
                'slots' => [],
                'working_hours' => null,
            ]);
        }

        if (!$schedule->isWorkingDate($date)) {
            return response()->json([
                'unlimited' => $schedule->hasUnlimitedAppointments(),
                'working' => false,
                'slots' => [],
                'working_hours' => null,
            ]);
        }

        $workingHours = $schedule->getWorkingHoursForDate($date);

        if (!$workingHours) {
            return response()->json([
                'unlimited' => $schedule->hasUnlimitedAppointments(),
                'working' => false,
                'slots' => [],
                'working_hours' => null,
            ]);
        }

        if ($schedule->hasUnlimitedAppointments()) {
            return response()->json([
                'unlimited' => true,
                'working' => true,
                'slots' => [],
                'working_hours' => $workingHours,
            ]);
        }

        $daySchedule = $schedule->getDaySchedule($date);
        $freeSlots = collect($daySchedule)
            ->filter(fn ($slot) => $slot['is_free'])
            ->pluck('time')
            ->values();

        return response()->json([
            'unlimited' => false,
            'working' => true,
            'slots' => $freeSlots,
            'working_hours' => $workingHours,
        ]);
    }

    public function days(Schedule $schedule, Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        return response()->json([
            'unlimited' => $schedule->hasUnlimitedAppointments(),
            'days' => $schedule->availabilityForMonth($request->query('month')),
        ]);
    }
}
