<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Catalog;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\SubCatalog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesBookingSchema;
use Tests\TestCase;

class SharedConsultationTest extends TestCase
{
    use CreatesBookingSchema;

    private Service $consultation;
    private SubCatalog $urologySub;
    private SubCatalog $cardioSub;
    private Schedule $urologistSchedule;
    private Schedule $cardiologistSchedule;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBookingSchema();
        $this->seedSharedConsultation();
    }

    public function test_booking_filters_doctors_by_sub_catalog(): void
    {
        $uro = $this->get(route('service.booking', [
            'service' => $this->consultation,
            'sub_catalog' => $this->urologySub->id,
        ]));
        $uro->assertOk();
        $uro->assertSee('Уролог Иван');
        $uro->assertDontSee('Кардиолог Пётр');

        $cardio = $this->get(route('service.booking', [
            'service' => $this->consultation,
            'sub_catalog' => $this->cardioSub->id,
        ]));
        $cardio->assertOk();
        $cardio->assertSee('Кардиолог Пётр');
        $cardio->assertDontSee('Уролог Иван');
    }

    public function test_booking_without_sub_catalog_asks_for_direction(): void
    {
        $response = $this->get(route('service.booking', $this->consultation));
        $response->assertOk();
        $response->assertSee('Выберите направление');
        $response->assertSee('Урология');
        $response->assertSee('Кардиология');
    }

    public function test_search_returns_consultation_per_direction(): void
    {
        $response = $this->getJson(route('api.search', ['q' => 'Консультативный']));
        $response->assertOk();

        $services = collect($response->json('services'));
        $this->assertGreaterThanOrEqual(2, $services->count());

        $urls = $services->pluck('url')->all();
        $this->assertTrue(collect($urls)->contains(fn ($url) => str_contains($url, 'sub_catalog=' . $this->urologySub->id)));
        $this->assertTrue(collect($urls)->contains(fn ($url) => str_contains($url, 'sub_catalog=' . $this->cardioSub->id)));
    }

    public function test_destroy_service_blocked_when_appointments_exist(): void
    {
        Appointment::create([
            'schedule_id' => $this->urologistSchedule->id,
            'service_id' => $this->consultation->id,
            'client_name' => 'Пациент',
            'client_phone' => '+7(701)111-22-33',
            'appointment_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:00',
            'total_price' => 8000,
            'status' => 'pending',
            'manage_token' => bin2hex(random_bytes(16)),
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.services.destroy', $this->consultation))
            ->assertRedirect(route('admin.services'));

        $this->assertDatabaseHas('services', [
            'id' => $this->consultation->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.services.destroy', $this->consultation), ['deactivate' => 1])
            ->assertRedirect(route('admin.services'));

        $this->assertDatabaseHas('services', [
            'id' => $this->consultation->id,
            'is_active' => false,
        ]);
    }

    public function test_merge_consultations_command_remaps_duplicates(): void
    {
        $name = 'Консультативный первичный прием врача';
        $this->consultation->update(['name' => $name]);

        $dup = Service::create([
            'name' => $name,
            'sub_catalog_id' => $this->cardioSub->id,
            'price' => 8000,
            'is_active' => true,
        ]);
        $dup->subCatalogs()->attach($this->cardioSub->id);
        $this->cardiologistSchedule->services()->attach($dup->id);

        $appointment = Appointment::create([
            'schedule_id' => $this->cardiologistSchedule->id,
            'service_id' => $dup->id,
            'client_name' => 'Пациент',
            'client_phone' => '+7(701)111-22-33',
            'appointment_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'appointment_time' => '11:00',
            'total_price' => 8000,
            'status' => 'pending',
            'manage_token' => bin2hex(random_bytes(16)),
        ]);

        $this->artisan('services:merge-consultations')
            ->assertSuccessful();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'service_id' => $this->consultation->id,
        ]);
        $this->assertDatabaseHas('services', [
            'id' => $dup->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('schedule_services', [
            'schedule_id' => $this->cardiologistSchedule->id,
            'service_id' => $this->consultation->id,
        ]);
    }

    private function seedSharedConsultation(): void
    {
        $this->admin = User::create([
            'name' => 'Админ',
            'login' => 'admin',
            'password' => Hash::make('password'),
            'role' => 1,
            'is_active' => true,
        ]);

        $uroCat = Catalog::create(['name' => 'Урология', 'is_active' => true]);
        $cardioCat = Catalog::create(['name' => 'Кардиология', 'is_active' => true]);
        $this->urologySub = SubCatalog::create(['name' => 'Приём', 'catalog_id' => $uroCat->id]);
        $this->cardioSub = SubCatalog::create(['name' => 'Приём', 'catalog_id' => $cardioCat->id]);

        $this->consultation = Service::create([
            'name' => 'Консультативный первичный прием врача',
            'sub_catalog_id' => $this->urologySub->id,
            'price' => 8000,
            'is_active' => true,
        ]);
        $this->consultation->subCatalogs()->attach([
            $this->urologySub->id,
            $this->cardioSub->id,
        ]);

        $cystoscopy = Service::create([
            'name' => 'Цистоскопия',
            'sub_catalog_id' => $this->urologySub->id,
            'price' => 15000,
            'is_active' => true,
        ]);
        $cystoscopy->subCatalogs()->attach($this->urologySub->id);

        $ecg = Service::create([
            'name' => 'ЭКГ',
            'sub_catalog_id' => $this->cardioSub->id,
            'price' => 5000,
            'is_active' => true,
        ]);
        $ecg->subCatalogs()->attach($this->cardioSub->id);

        $urologist = User::create([
            'name' => 'Уролог Иван',
            'login' => 'urologist',
            'password' => Hash::make('password'),
            'role' => 4,
            'specialization' => 'Уролог',
            'is_active' => true,
        ]);
        $cardiologist = User::create([
            'name' => 'Кардиолог Пётр',
            'login' => 'cardiologist',
            'password' => Hash::make('password'),
            'role' => 4,
            'specialization' => 'Кардиолог',
            'is_active' => true,
        ]);

        $hours = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $hours[$day . '_active'] = true;
            $hours[$day . '_start'] = '09:00:00';
            $hours[$day . '_end'] = '12:00:00';
        }

        $this->urologistSchedule = Schedule::create(array_merge($hours, [
            'user_id' => $urologist->id,
            'appointment_interval' => 30,
            'unlimited_appointments' => false,
            'is_active' => true,
            'start_date' => Carbon::today()->subDay()->format('Y-m-d'),
            'end_date' => Carbon::today()->addMonth()->format('Y-m-d'),
        ]));
        $this->urologistSchedule->services()->attach([
            $this->consultation->id,
            $cystoscopy->id,
        ]);

        $this->cardiologistSchedule = Schedule::create(array_merge($hours, [
            'user_id' => $cardiologist->id,
            'appointment_interval' => 30,
            'unlimited_appointments' => false,
            'is_active' => true,
            'start_date' => Carbon::today()->subDay()->format('Y-m-d'),
            'end_date' => Carbon::today()->addMonth()->format('Y-m-d'),
        ]));
        $this->cardiologistSchedule->services()->attach([
            $this->consultation->id,
            $ecg->id,
        ]);
    }
}
