<?php
// app/Models/Schedule.php (обновленная версия)
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'appointment_interval',
        'unlimited_appointments', // Новое поле
        'monday_active',
        'monday_start',
        'monday_end',
        'tuesday_active',
        'tuesday_start',
        'tuesday_end',
        'wednesday_active',
        'wednesday_start',
        'wednesday_end',
        'thursday_active',
        'thursday_start',
        'thursday_end',
        'friday_active',
        'friday_start',
        'friday_end',
        'saturday_active',
        'saturday_start',
        'saturday_end',
        'sunday_active',
        'sunday_start',
        'sunday_end',
        'is_active',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'appointment_date' => 'date:Y-m-d',
        'monday_active' => 'boolean',
        'tuesday_active' => 'boolean',
        'wednesday_active' => 'boolean',
        'thursday_active' => 'boolean',
        'friday_active' => 'boolean',
        'saturday_active' => 'boolean',
        'sunday_active' => 'boolean',
        'unlimited_appointments' => 'boolean',
        'is_active' => 'boolean',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'schedule_services');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scheduleDates(): HasMany
    {
        return $this->hasMany(ScheduleDate::class);
    }

    public function getWorkingDays(): array
    {
        $days = [];
        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($weekDays as $day) {
            if ($this->{$day . '_active'}) {
                $days[$day] = [
                    'start' => $this->{$day . '_start'},
                    'end' => $this->{$day . '_end'}
                ];
            }
        }

        return $days;
    }

    public function isWorkingDay(string $dayName): bool
    {
        return $this->{strtolower($dayName) . '_active'};
    }

    /**
     * Проверяет, является ли конкретная дата рабочим днем
     */
    public function isWorkingDate(string $date): bool
    {
        // Сначала проверяем конкретные даты (используем уже загруженные данные, если есть)
        if ($this->relationLoaded('scheduleDates')) {
            $scheduleDate = $this->scheduleDates->first(function ($sd) use ($date) {
                return $sd->date->format('Y-m-d') === $date && $sd->is_active;
            });
        } else {
            $scheduleDate = $this->scheduleDates()
                ->where('date', $date)
                ->where('is_active', true)
                ->first();
        }
        
        if ($scheduleDate) {
            return true;
        }

        // Если нет конкретной даты, проверяем день недели
        $dayName = strtolower(Carbon::parse($date)->format('l'));
        return $this->isWorkingDay($dayName);
    }

    public function getWorkingHours(string $dayName): ?array
    {
        $day = strtolower($dayName);
        if (!$this->{$day . '_active'}) {
            return null;
        }

        return [
            'start' => $this->{$day . '_start'},
            'end' => $this->{$day . '_end'}
        ];
    }

    /**
     * Получает рабочие часы для конкретной даты
     */
    public function getWorkingHoursForDate(string $date): ?array
    {
        // Сначала проверяем конкретные даты (используем уже загруженные данные, если есть)
        if ($this->relationLoaded('scheduleDates')) {
            $scheduleDate = $this->scheduleDates->first(function ($sd) use ($date) {
                return $sd->date->format('Y-m-d') === $date && $sd->is_active;
            });
        } else {
            $scheduleDate = $this->scheduleDates()
                ->where('date', $date)
                ->where('is_active', true)
                ->first();
        }
        
        if ($scheduleDate && $scheduleDate->start_time && $scheduleDate->end_time) {
            return [
                'start' => $scheduleDate->start_time,
                'end' => $scheduleDate->end_time
            ];
        }

        // Если нет конкретной даты, используем день недели
        $dayName = strtolower(Carbon::parse($date)->format('l'));
        return $this->getWorkingHours($dayName);
    }

    // НОВАЯ ЛОГИКА: генерация временных слотов на основе интервала графика
    // ОБНОВЛЕННАЯ ЛОГИКА: генерация временных слотов с учетом неограниченных записей
    public function generateTimeSlots(string $date): array
    {
        // Проверяем, является ли дата рабочим днем (с учетом конкретных дат)
        if (!$this->isWorkingDate($date)) {
            return [];
        }

        // Если неограниченное количество записей - возвращаем общий слот
        if ($this->unlimited_appointments) {
            return ['any_time']; // Специальный маркер
        }

        // Получаем рабочие часы для конкретной даты (с учетом конкретных дат)
        $workingHours = $this->getWorkingHoursForDate($date);
        if (!$workingHours || !$this->appointment_interval) {
            return [];
        }

        $slots = [];
        $start = Carbon::parse($workingHours['start']);
        $end = Carbon::parse($workingHours['end']);
        $interval = $this->appointment_interval;

        while ($start->copy()->addMinutes($interval)->lte($end)) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($interval);
        }

        return $slots;
    }

    // Проверка доступности временного слота
    // Проверка доступности с учетом неограниченных записей
    public function isTimeSlotAvailable(string $date, string $time = null, int $excludeAppointmentId = null): bool
    {
        // Если неограниченное количество записей - всегда доступно
        if ($this->unlimited_appointments) {
            return true;
        }

        // Если нет времени для неограниченных записей
        if (!$time) {
            return $this->unlimited_appointments;
        }

        // Стандартная проверка для ограниченных записей
        if (!$this->appointment_interval) {
            return true;
        }

        $startTime = Carbon::parse($time);
        $endTime = $startTime->copy()->addMinutes($this->appointment_interval);

        $query = Appointment::where('schedule_id', $this->id)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($time, $endTime) {
                $q->where(function ($subQ) use ($time, $endTime) {
                    $subQ->where('appointment_time', '<=', $time)
                        ->where('appointment_end_time', '>', $time);
                })->orWhere(function ($subQ) use ($time, $endTime) {
                    $subQ->where('appointment_time', '<', $endTime->format('H:i'))
                        ->where('appointment_end_time', '>=', $endTime->format('H:i'));
                })->orWhere(function ($subQ) use ($time, $endTime) {
                    $subQ->where('appointment_time', '>=', $time)
                        ->where('appointment_end_time', '<=', $endTime->format('H:i'));
                });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->count() === 0;
    }

    public function hasUnlimitedAppointments(): bool
    {
        return $this->unlimited_appointments || !$this->appointment_interval;
    }

    public function getFormattedInterval(): string
    {
        if ($this->unlimited_appointments || !$this->appointment_interval) {
            return 'Неограниченно';
        }

        if ($this->appointment_interval >= 60) {
            $hours = intdiv($this->appointment_interval, 60);
            $minutes = $this->appointment_interval % 60;
            return $minutes > 0 ? "{$hours}ч {$minutes}мин" : "{$hours}ч";
        }
        return "{$this->appointment_interval}мин";
    }

    // Получить все записи на конкретную дату
    public function getAppointmentsForDate(string $date)
    {
        return Appointment::where('schedule_id', $this->id)
            ->whereDate('appointment_date', $date)
            ->with(['service'])
            ->orderBy('appointment_time')
            ->get();
    }

    // Получить расписание на день с пустыми слотами
    public function getDaySchedule(string $date): array
    {
        $appointments = $this->getAppointmentsForDate($date);
        $timeSlots = $this->generateTimeSlots($date);

        $schedule = [];

        if ($this->hasUnlimitedAppointments()) {
            // Для неограниченных записей показываем все записи без привязки к слотам
            foreach ($appointments as $appointment) {
                $schedule[] = [
                    'time' => $appointment->appointment_time ? Carbon::parse($appointment->appointment_time)->format('H:i') : 'Без времени',
                    'appointment' => $appointment,
                    'is_free' => false
                ];
            }
        } else {
            // Для ограниченных записей показываем слоты
            foreach ($timeSlots as $slot) {
                $appointment = $appointments->first(function ($app) use ($slot) {
                    return $app->appointment_time && Carbon::parse($app->appointment_time)->format('H:i') === $slot;
                });

                $schedule[] = [
                    'time' => $slot,
                    'appointment' => $appointment,
                    'is_free' => !$appointment
                ];
            }
        }

        return $schedule;
    }

    // Получить занятые временные слоты для определенной даты
    public function getBookedTimeSlots(string $date): array
    {
        return Appointment::with('service')
            ->where('schedule_id', $this->id)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('appointment_time')
            ->get()
            ->map(function ($appointment) {
                return [
                    'start' => $appointment->appointment_time ? Carbon::parse($appointment->appointment_time)->format('H:i') : null,
                    'end' => $appointment->appointment_end_time ? Carbon::parse($appointment->appointment_end_time)->format('H:i') : null,
                    'client_name' => $appointment->client_name,
                    'service_name' => $appointment->service ? $appointment->service->name : null,
                    'appointment_id' => $appointment->id,
                    'patient_iin' => $appointment->patient_iin,
                ];
            })
            ->toArray();
    }

    public function getDayNameInRussian(string $day): string
    {
        $days = [
            'monday' => 'Понедельник',
            'tuesday' => 'Вторник',
            'wednesday' => 'Среда',
            'thursday' => 'Четверг',
            'friday' => 'Пятница',
            'saturday' => 'Суббота',
            'sunday' => 'Воскресенье',
        ];

        return $days[$day] ?? $day;
    }
}
