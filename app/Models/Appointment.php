<?php
// App\Models\Appointment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'notes'
    ];

    protected $casts = [
        'appointment_date' => 'datetime:Y-m-d',
        'appointment_time' => 'datetime:H:i',
        'appointment_end_time' => 'datetime:H:i',
        'total_price' => 'decimal:2'
    ];
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
