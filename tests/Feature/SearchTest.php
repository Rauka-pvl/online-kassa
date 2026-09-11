<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\SubCatalog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesBookingSchema;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use CreatesBookingSchema;

    private Service $service;
    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBookingSchema();

        $this->doctor = User::create([
            'name' => 'Доктор Тест',
            'login' => 'doctor',
            'password' => Hash::make('password'),
            'role' => 4,
            'specialization' => 'Терапевт',
            'is_active' => true,
        ]);

        $catalog = Catalog::create(['name' => 'Терапия', 'is_active' => true]);
        $sub = SubCatalog::create(['name' => 'Общая', 'catalog_id' => $catalog->id]);
        $this->service = Service::create([
            'name' => 'Консультация терапевта',
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

        $schedule = Schedule::create(array_merge($hours, [
            'user_id' => $this->doctor->id,
            'appointment_interval' => 30,
            'unlimited_appointments' => false,
            'is_active' => true,
            'start_date' => Carbon::today()->subDay()->format('Y-m-d'),
            'end_date' => Carbon::today()->addMonth()->format('Y-m-d'),
        ]));
        $schedule->services()->attach($this->service->id);
    }

    public function test_search_finds_doctors_and_services(): void
    {
        $doctorSearch = $this->getJson(route('api.search', ['q' => 'Доктор']));
        $doctorSearch->assertOk()->assertJsonPath('doctors.0.name', 'Доктор Тест');
        $this->assertNotEmpty($doctorSearch->json('doctors.0.url'));

        $serviceSearch = $this->getJson(route('api.search', ['q' => 'Консультация']));
        $serviceSearch->assertOk()->assertJsonPath('services.0.name', 'Консультация терапевта');
        $this->assertNotEmpty($serviceSearch->json('services.0.url'));
    }

    public function test_search_requires_two_characters(): void
    {
        $this->getJson(route('api.search', ['q' => 'т']))
            ->assertOk()
            ->assertJsonPath('doctors', [])
            ->assertJsonPath('services', []);
    }

    public function test_doctor_page_lists_bookable_services(): void
    {
        $this->get(route('doctor.show', $this->doctor))
            ->assertOk()
            ->assertSee('Доктор Тест')
            ->assertSee('Консультация терапевта');
    }
}
