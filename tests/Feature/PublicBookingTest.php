<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Catalog;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\StaffNotification;
use App\Models\SubCatalog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesBookingSchema;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use CreatesBookingSchema;

    private Service $service;
    private Schedule $schedule;
    private User $registrar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBookingSchema();
        $this->seedBookingContext();
    }

    public function test_patient_can_book_without_payment_and_staff_is_notified(): void
    {
        $date = Carbon::today()->format('Y-m-d');

        $response = $this->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'schedule_id' => $this->schedule->id,
            'date' => $date,
            'time' => '09:00',
            'client_name' => 'Иван Петров',
            'client_phone' => '+7(701)111-22-33',
        ]);

        $appointment = Appointment::first();
        $this->assertNotNull($appointment);
        $this->assertSame('pending', $appointment->status);
        $this->assertNotEmpty($appointment->manage_token);

        $response->assertRedirect(route('booking.show', $appointment->manage_token));

        $this->assertSame(1, StaffNotification::where('type', 'booking_created')->count());
        $this->assertSame($this->registrar->id, StaffNotification::first()->user_id);
    }

    public function test_same_slot_cannot_be_booked_twice(): void
    {
        $payload = [
            'service_id' => $this->service->id,
            'schedule_id' => $this->schedule->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'time' => '09:00',
            'client_name' => 'Иван Петров',
            'client_phone' => '+7(701)111-22-33',
        ];

        $this->post(route('booking.store'), $payload)->assertRedirect();
        $this->post(route('booking.store'), $payload)->assertSessionHasErrors('time');
        $this->assertSame(1, Appointment::count());
    }

    public function test_patient_can_cancel_by_token_and_staff_is_notified(): void
    {
        $this->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'schedule_id' => $this->schedule->id,
            'date' => Carbon::today()->addDay()->format('Y-m-d'),
            'time' => '09:00',
            'client_name' => 'Иван Петров',
            'client_phone' => '+7(701)111-22-33',
        ]);

        $appointment = Appointment::first();

        $this->post(route('booking.cancel', $appointment->manage_token))
            ->assertRedirect(route('booking.show', $appointment->manage_token));

        $appointment->refresh();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertSame('patient', $appointment->cancelled_by);
        $this->assertTrue($this->schedule->isTimeSlotAvailable(
            $appointment->appointment_date->format('Y-m-d'),
            '09:00'
        ));
        $this->assertSame(1, StaffNotification::where('type', 'booking_cancelled')->count());
    }

    public function test_cancelled_slot_can_be_booked_again(): void
    {
        $payload = [
            'service_id' => $this->service->id,
            'schedule_id' => $this->schedule->id,
            'date' => Carbon::today()->addDay()->format('Y-m-d'),
            'time' => '09:00',
            'client_name' => 'Иван Петров',
            'client_phone' => '+7(701)111-22-33',
        ];

        $this->post(route('booking.store'), $payload);
        $appointment = Appointment::first();
        $this->post(route('booking.cancel', $appointment->manage_token));

        $this->post(route('booking.store'), array_merge($payload, [
            'client_name' => 'Анна Сидорова',
            'client_phone' => '+7(702)222-33-44',
        ]))->assertRedirect();

        $this->assertSame(2, Appointment::count());
        $this->assertSame(1, Appointment::where('status', 'pending')->count());
    }

    public function test_cannot_cancel_twice_or_past_appointment(): void
    {
        $this->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'schedule_id' => $this->schedule->id,
            'date' => Carbon::today()->addDay()->format('Y-m-d'),
            'time' => '09:00',
            'client_name' => 'Иван Петров',
            'client_phone' => '+7(701)111-22-33',
        ]);

        $appointment = Appointment::first();
        $this->post(route('booking.cancel', $appointment->manage_token));
        $this->post(route('booking.cancel', $appointment->manage_token))
            ->assertRedirect()
            ->assertSessionHas('error');

        $past = Appointment::create([
            'schedule_id' => $this->schedule->id,
            'service_id' => $this->service->id,
            'client_name' => 'Прошлый Пациент',
            'client_phone' => '+7(700)000-00-00',
            'appointment_date' => Carbon::yesterday()->format('Y-m-d'),
            'appointment_time' => '10:00',
            'total_price' => 0,
            'status' => 'pending',
        ]);

        $this->post(route('booking.cancel', $past->manage_token))->assertSessionHas('error');
        $this->assertSame('pending', $past->fresh()->status);
    }

    public function test_incomplete_phone_is_rejected(): void
    {
        $this->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'schedule_id' => $this->schedule->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'time' => '09:00',
            'client_name' => 'Иван Петров',
            'client_phone' => '+7 (701) 11',
        ])->assertSessionHasErrors('client_phone');

        $this->assertSame(0, Appointment::count());

        $this->post(route('booking.lookup'), [
            'client_phone' => '+7 (701)',
            'code' => 'ASK-1',
        ])->assertSessionHasErrors('client_phone');
    }

    public function test_lookup_by_phone_and_code(): void
    {
        $this->post(route('booking.store'), [
            'service_id' => $this->service->id,
            'schedule_id' => $this->schedule->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'time' => '10:00',
            'client_name' => 'Иван Петров',
            'client_phone' => '+7(701)111-22-33',
        ]);

        $appointment = Appointment::first();

        $this->post(route('booking.lookup'), [
            'client_phone' => '77011112233',
            'code' => $appointment->code,
        ])->assertRedirect(route('booking.show', $appointment->manage_token));
    }

    public function test_calendar_marks_days_with_free_slots(): void
    {
        $month = Carbon::today()->format('Y-m');
        $today = Carbon::today()->format('Y-m-d');

        $response = $this->getJson(route('api.schedules.days', [
            'schedule' => $this->schedule,
            'month' => $month,
        ]));

        $response->assertOk()->assertJsonPath('unlimited', false);

        $todayInfo = collect($response->json('days'))->firstWhere('date', $today);
        $this->assertNotNull($todayInfo);
        $this->assertSame('open', $todayInfo['status']);
        $this->assertGreaterThan(0, $todayInfo['free']);
    }

    private function seedBookingContext(): void
    {
        $this->registrar = User::create([
            'name' => 'Регистратор',
            'login' => 'registrar',
            'password' => Hash::make('password'),
            'role' => 3,
            'is_active' => true,
        ]);

        $doctor = User::create([
            'name' => 'Доктор Тест',
            'login' => 'doctor',
            'password' => Hash::make('password'),
            'role' => 4,
            'is_active' => true,
        ]);

        $catalog = Catalog::create(['name' => 'Терапия', 'is_active' => true]);
        $sub = SubCatalog::create(['name' => 'Общая', 'catalog_id' => $catalog->id]);
        $this->service = Service::create([
            'name' => 'Консультация',
            'sub_catalog_id' => $sub->id,
            'price' => 8000,
            'is_active' => true,
        ]);
        $this->service->subCatalogs()->attach($sub->id);

        $hours = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $hours[$day . '_active'] = true;
            $hours[$day . '_start'] = '09:00:00';
            $hours[$day . '_end'] = '12:00:00';
        }

        $this->schedule = Schedule::create(array_merge($hours, [
            'user_id' => $doctor->id,
            'appointment_interval' => 30,
            'unlimited_appointments' => false,
            'is_active' => true,
            'start_date' => Carbon::today()->subDay()->format('Y-m-d'),
            'end_date' => Carbon::today()->addMonth()->format('Y-m-d'),
        ]));

        $this->schedule->services()->attach($this->service->id);
    }
}
