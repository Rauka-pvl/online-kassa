<?php
// App\Models\Appointment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'registrar_id',
        'service_id',
        'client_name',
        'patient_iin',
        'client_phone',
        'client_email',
        'appointment_date',
        'appointment_time',
        'appointment_end_time',
        'total_price',
        'status',
        'notes',
        'manage_token',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'appointment_date' => 'datetime:Y-m-d',
        'appointment_time' => 'datetime:H:i',
        'appointment_end_time' => 'datetime:H:i',
        'total_price' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            if (empty($appointment->manage_token)) {
                $appointment->manage_token = static::generateManageToken();
            }
        });
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public static function generateManageToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('manage_token', $token)->exists());

        return $token;
    }

    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    public static function isCompletePhone(?string $phone): bool
    {
        $digits = static::normalizePhone((string) $phone);

        return strlen($digits) === 11 && str_starts_with($digits, '7');
    }

    public function getCodeAttribute(): string
    {
        return 'ASK-' . $this->id;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Ожидает подтверждения',
            'confirmed' => 'Подтверждена',
            'completed' => 'Завершена',
            'cancelled' => 'Отменена',
            default => $this->status,
        };
    }

    public function formattedTime(): ?string
    {
        if (!$this->appointment_time) {
            return null;
        }

        return Carbon::parse($this->appointment_time)->format('H:i');
    }

    public function formattedDate(): string
    {
        return Carbon::parse($this->appointment_date)->format('d.m.Y');
    }

    public function startsAt(): Carbon
    {
        $date = Carbon::parse($this->appointment_date)->startOfDay();

        if ($this->appointment_time) {
            $time = Carbon::parse($this->appointment_time);
            $date->setTime($time->hour, $time->minute, $time->second);
        }

        return $date;
    }

    public function canBeCancelledByPatient(): bool
    {
        if (in_array($this->status, ['cancelled', 'completed'], true)) {
            return false;
        }

        return $this->startsAt()->greaterThan(now());
    }

    public function cancelByPatient(): bool
    {
        if (!$this->canBeCancelledByPatient()) {
            return false;
        }

        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->cancelled_by = 'patient';

        return $this->save();
    }
}
