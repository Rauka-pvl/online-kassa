<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\SubCatalog;
use App\Models\Service;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Dashboard
    public function dashboard(): View
    {
        $stats = [
            'catalogs_count' => Catalog::count(),
            'subcatalogs_count' => SubCatalog::count(),
            'services_count' => Service::count(),
            'doctors_count' => User::where('role', 4)->count(),
            'registrars_count' => User::where('role', 3)->count(),
            'schedules_count' => Schedule::count(),
            'appointments_count' => Appointment::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // CATALOGS CRUD
    public function catalogs(Request $request): View
    {
        $query = Catalog::with('subCatalogs');

        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['id', 'name', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $catalogs = $query->paginate(15)->withQueryString();
        return view('admin.catalogs.index', compact('catalogs'));
    }

    public function createCatalog(): View
    {
        return view('admin.catalogs.create');
    }

    public function storeCatalog(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('catalogs', 'public');
        }

        Catalog::create($data);

        return redirect()->route('admin.catalogs')->with('success', 'Каталог успешно создан');
    }

    public function editCatalog(Catalog $catalog): View
    {
        return view('admin.catalogs.edit', compact('catalog'));
    }

    public function updateCatalog(Request $request, Catalog $catalog): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($catalog->image) {
                Storage::disk('public')->delete($catalog->image);
            }
            $data['image'] = $request->file('image')->store('catalogs', 'public');
        }

        $catalog->update($data);

        return redirect()->route('admin.catalogs')->with('success', 'Каталог успешно обновлен');
    }

    public function destroyCatalog(Catalog $catalog): RedirectResponse
    {
        if ($catalog->image) {
            Storage::disk('public')->delete($catalog->image);
        }
        $catalog->delete();
        return redirect()->route('admin.catalogs')->with('success', 'Каталог успешно удален');
    }

    // SUB CATALOGS CRUD
    public function subCatalogs(Request $request): View
    {
        $query = SubCatalog::with('catalog');

        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Фильтр по каталогу
        if ($request->filled('catalog_filter')) {
            $query->where('catalog_id', $request->catalog_filter);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['id', 'name', 'catalog_id', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $subCatalogs = $query->paginate(15)->withQueryString();
        $catalogs = Catalog::all(); // Для фильтра
        return view('admin.subcatalogs.index', compact('subCatalogs', 'catalogs'));
    }

    public function createSubCatalog(): View
    {
        $catalogs = Catalog::all();
        return view('admin.subcatalogs.create', compact('catalogs'));
    }

    public function storeSubCatalog(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'catalog_id' => 'required|exists:catalogs,id'
        ]);

        SubCatalog::create($request->all());

        return redirect()->route('admin.subcatalogs')->with('success', 'Подкаталог успешно создан');
    }

    public function editSubCatalog(SubCatalog $subcatalog): View
    {
        $catalogs = Catalog::all();
        return view('admin.subcatalogs.edit', compact('subcatalog', 'catalogs'));
    }

    public function updateSubCatalog(Request $request, SubCatalog $subcatalog): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'catalog_id' => 'required|exists:catalogs,id'
        ]);

        $subcatalog->update($request->all());

        return redirect()->route('admin.subcatalogs')->with('success', 'Подкаталог успешно обновлен');
    }

    public function destroySubCatalog(SubCatalog $subcatalog): RedirectResponse
    {
        $subcatalog->delete();
        return redirect()->route('admin.subcatalogs')->with('success', 'Подкаталог успешно удален');
    }

    // SERVICES CRUD
    public function services(Request $request): View
    {
        $query = Service::with(['subCatalog.catalog']);

        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Фильтр по подкаталогу
        if ($request->filled('subcatalog_filter')) {
            $query->where('sub_catalog_id', $request->subcatalog_filter);
        }

        // Фильтр по каталогу
        if ($request->filled('catalog_filter')) {
            $query->whereHas('subCatalog', function ($q) use ($request) {
                $q->where('catalog_id', $request->catalog_filter);
            });
        }

        // Фильтр по статусу
        if ($request->filled('status_filter')) {
            $query->where('is_active', $request->status_filter == 'active' ? 1 : 0);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['id', 'name', 'price', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $services = $query->paginate(15)->withQueryString();
        $subCatalogs = SubCatalog::with('catalog')->get();
        $catalogs = Catalog::all();
        return view('admin.services.index', compact('services', 'subCatalogs', 'catalogs'));
    }

    public function createService(): View
    {
        $subCatalogs = SubCatalog::with('catalog')->get();
        return view('admin.services.create', compact('subCatalogs'));
    }

    public function storeService(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sub_catalog_id' => 'required|exists:sub_catalogs,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        Service::create($request->all());

        return redirect()->route('admin.services')->with('success', 'Услуга успешно создана');
    }

    public function editService(Service $service): View
    {
        $subCatalogs = SubCatalog::with('catalog')->get();
        return view('admin.services.edit', compact('service', 'subCatalogs'));
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sub_catalog_id' => 'required|exists:sub_catalogs,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $service->update($request->all());

        return redirect()->route('admin.services')->with('success', 'Услуга успешно обновлена');
    }

    public function destroyService(Service $service): RedirectResponse
    {
        $service->delete();
        return redirect()->route('admin.services')->with('success', 'Услуга успешно удалена');
    }

    // USERS (DOCTORS & REGISTRARS) CRUD
    public function users(Request $request): View
    {
        $query = User::whereIn('role', [2, 3, 4]);

        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('login', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('specialization', 'like', '%' . $search . '%');
            });
        }

        // Фильтр по роли
        if ($request->filled('role_filter')) {
            $query->where('role', $request->role_filter);
        }

        // Фильтр по статусу
        if ($request->filled('status_filter')) {
            $query->where('is_active', $request->status_filter == 'active' ? 1 : 0);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['id', 'name', 'login', 'role', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $users = $query->paginate(15)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function createUser(): View
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validation = $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:2,3,4',
            'specialization' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $validation['password'] = Hash::make($request->password);

        User::create($validation);

        return redirect()->route('admin.users')->with('success', 'Пользователь успешно создан');
    }

    public function editUser(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:2,3,4',
            'specialization' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'Пользователь успешно обновлен');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Пользователь успешно удален');
    }

    // SCHEDULES CRUD
    public function schedules(Request $request): View
    {
        $query = Schedule::with(['user', 'services']);

        // Поиск по имени врача
        if ($request->filled('doctor_search')) {
            $searchTerm = $request->doctor_search;
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%');
            });
        }

        // Фильтр по статусу
        if ($request->filled('status_filter')) {
            $query->where('is_active', $request->status_filter == 'active' ? 1 : 0);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['id', 'created_at', 'start_date', 'end_date'];

        if ($sortBy === 'doctor_name') {
            // Сортировка по имени врача через join с избежанием дубликатов
            $query->leftJoin('users', 'schedules.user_id', '=', 'users.id')
                ->select('schedules.*')
                ->orderBy('users.name', $sortOrder)
                ->groupBy('schedules.id');
        } elseif (in_array($sortBy, $allowedSorts)) {
            $query->orderBy('schedules.' . $sortBy, $sortOrder);
        }

        $schedules = $query->paginate(15)->withQueryString();
        return view('admin.schedules.index', compact('schedules'));
    }

    public function createSchedule(): View
    {
        $doctors = User::where('role', 4)->where('is_active', true)->get();

        // Загружаем каталоги с подкаталогами и активными услугами
        $catalogs = Catalog::with([
            'subCatalogs.services' => function ($query) {
                $query->where('is_active', true);
            }
        ])
            ->whereHas('subCatalogs.services', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        // Фильтруем подкаталоги без активных услуг на уровне коллекции
        $catalogs->each(function ($catalog) {
            $catalog->setRelation(
                'subCatalogs',
                $catalog->subCatalogs->filter(function ($subCatalog) {
                    return $subCatalog->services->count() > 0;
                })
            );
        });

        return view('admin.schedules.create', compact('doctors', 'catalogs'));
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'appointment_type' => 'required|in:limited,unlimited',
            'appointment_interval' => 'required_if:appointment_type,limited|nullable|integer|min:5|max:480',
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
            'is_active' => 'boolean'
        ]);

        $scheduleData = $request->only([
            'user_id',
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
        ]);

        // Устанавливаем параметры интервала
        if ($request->appointment_type === 'unlimited') {
            $scheduleData['unlimited_appointments'] = true;
            $scheduleData['appointment_interval'] = null;
        } else {
            $scheduleData['unlimited_appointments'] = false;
            $scheduleData['appointment_interval'] = $request->appointment_interval;
        }

        $schedule = Schedule::create($scheduleData);
        $schedule->services()->attach($request->services);

        // Сохраняем конкретные даты, если они указаны
        if ($request->filled('schedule_dates')) {
            foreach ($request->schedule_dates as $dateData) {
                if (!empty($dateData['date'])) {
                    $schedule->scheduleDates()->create([
                        'date' => $dateData['date'],
                        'start_time' => $dateData['start_time'] ?? null,
                        'end_time' => $dateData['end_time'] ?? null,
                        'is_active' => $dateData['is_active'] ?? true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.schedules')->with('success', 'График успешно создан');
    }

    public function editSchedule(Schedule $schedule): View
    {
        $doctors = User::where('role', 4)->where('is_active', true)->get();
        
        // Загружаем конкретные даты графика
        $schedule->load('scheduleDates');

        // Загружаем каталоги с подкаталогами и активными услугами
        $catalogs = Catalog::with([
            'subCatalogs.services' => function ($query) {
                $query->where('is_active', true);
            }
        ])
            ->whereHas('subCatalogs.services', function ($query) {
                $query->where('is_active', true);
            })
            ->get();

        // Фильтруем подкаталоги без активных услуг на уровне коллекции
        $catalogs->each(function ($catalog) {
            $catalog->setRelation(
                'subCatalogs',
                $catalog->subCatalogs->filter(function ($subCatalog) {
                    return $subCatalog->services->count() > 0;
                })
            );
        });

        return view('admin.schedules.edit', compact('schedule', 'doctors', 'catalogs'));
    }

    public function updateSchedule(Request $request, Schedule $schedule): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            // 'appointment_interval' => 'required|integer|min:5|max:480',
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
            'is_active' => 'boolean',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable|after_or_equal:start_date',

        ]);

        $scheduleData = $request->only([
            'user_id',
            'appointment_interval',
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
        ]);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            $scheduleData["{$day}_active"] = $request->boolean("{$day}_active", false);
            $scheduleData["{$day}_start"]  = $scheduleData["{$day}_active"] ? $request->input("{$day}_start") : null;
            $scheduleData["{$day}_end"]    = $scheduleData["{$day}_active"] ? $request->input("{$day}_end") : null;
        }

        $schedule->update($scheduleData);
        $schedule->services()->sync($request->services);

        // Обновляем конкретные даты
        if ($request->filled('schedule_dates')) {
            // Удаляем старые даты
            $schedule->scheduleDates()->delete();
            
            // Добавляем новые даты
            foreach ($request->schedule_dates as $dateData) {
                if (!empty($dateData['date'])) {
                    $schedule->scheduleDates()->create([
                        'date' => $dateData['date'],
                        'start_time' => $dateData['start_time'] ?? null,
                        'end_time' => $dateData['end_time'] ?? null,
                        'is_active' => $dateData['is_active'] ?? true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.schedules')->with('success', 'График успешно обновлен');
    }

    public function destroySchedule(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();
        return redirect()->route('admin.schedules')->with('success', 'График успешно удален');
    }

    // AJAX методы для админа (аналогичные регистратору)
    // public function getSchedulesAdmin(User $doctor)
    // {
    //     $schedules = $doctor->schedules()
    //         ->where('is_active', true)
    //         ->with('services')
    //         ->get();

    //     return response()->json($schedules->map(function ($schedule) {
    //         $workingDays = $schedule->getWorkingDays();
    //         $dayNames = [];

    //         foreach ($workingDays as $day => $hours) {
    //             $dayNames[] = $this->getDayNameRussian($day);
    //         }

    //         return [
    //             'id' => $schedule->id,
    //             'name' => 'График ' . $schedule->id . ' (' . implode(', ', $dayNames) . ')',
    //             'working_days' => $workingDays,
    //             'services_count' => $schedule->services->count()
    //         ];
    //     }));
    // }

    public function getServicesAdmin(Schedule $schedule)
    {
        $services = $schedule->services()->where('is_active', true)->get();

        return response()->json($services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->formatted_price,
                'description' => $service->description
            ];
        }));
    }

    public function getTimeSlotsAdmin(Schedule $schedule, string $date)
    {
        // Проверяем, работает ли врач в этот день (с учетом конкретных дат)
        if (!$schedule->isWorkingDate($date)) {
            return response()->json([]);
        }

        // Генерируем все возможные слоты на основе интервала графика
        $allSlots = $schedule->generateTimeSlots($date);

        // Получаем занятые слоты
        $bookedSlots = $schedule->getBookedTimeSlots($date);
        $bookedTimes = array_column($bookedSlots, 'start');

        // Исключаем занятые слоты
        $availableSlots = array_diff($allSlots, $bookedTimes);

        return response()->json([
            'available_slots' => array_values($availableSlots),
            'booked_slots' => $bookedSlots,
            'interval_minutes' => $schedule->appointment_interval
        ]);
    }

    // // Вспомогательные методы (дублируем из RegistrarController)
    // private function isDateAvailable(Schedule $schedule, string $date): bool
    // {
    //     $dayName = strtolower(Carbon::parse($date)->format('l'));
    //     return $schedule->{$dayName . '_active'} ?? false;
    // }

    // private function isTimeSlotAvailable(Schedule $schedule, Service $service, string $date, string $time, int $excludeAppointmentId = null): bool
    // {
    //     $query = Appointment::where('schedule_id', $schedule->id)
    //         ->where('service_id', $service->id)
    //         ->whereDate('appointment_date', $date)
    //         ->where('appointment_time', $time)
    //         ->where('status', '!=', 'cancelled');

    //     if ($excludeAppointmentId) {
    //         $query->where('id', '!=', $excludeAppointmentId);
    //     }

    //     return $query->count() === 0;
    // }

    private function getDayNameRussian(string $day): string
    {
        $days = [
            'monday' => 'Пн',
            'tuesday' => 'Вт',
            'wednesday' => 'Ср',
            'thursday' => 'Чт',
            'friday' => 'Пт',
            'saturday' => 'Сб',
            'sunday' => 'Вс',
        ];

        return $days[$day] ?? $day;
    }

    // REPORTS
    public function reports(): View
    {
        return view('admin.reports.index');
    }

    public function generateReport(Request $request): View
    {
        $request->validate([
            'report_type' => 'required|in:appointments,services,doctors,revenue',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from'
        ]);

        $reportData = [];

        switch ($request->report_type) {
            case 'appointments':
                $reportData = Appointment::with(['schedule.user', 'service'])
                    ->whereBetween('appointment_date', [$request->date_from, $request->date_to])
                    ->orderBy('appointment_date', 'desc')
                    ->get();
                break;
            case 'services':
                $reportData = Service::with('subCatalog')
                    ->withCount(['appointments' => function ($query) use ($request) {
                        $query->whereBetween('appointment_date', [$request->date_from, $request->date_to]);
                    }])
                    ->get();
                break;
            case 'doctors':
                $reportData = User::where('role', 2)
                    ->with(['schedules' => function ($query) use ($request) {
                        $query->whereHas('appointments', function ($q) use ($request) {
                            $q->whereBetween('appointment_date', [$request->date_from, $request->date_to]);
                        });
                    }])
                    ->withCount(['schedules' => function ($query) use ($request) {
                        $query->whereHas('appointments', function ($q) use ($request) {
                            $q->whereBetween('appointment_date', [$request->date_from, $request->date_to]);
                        });
                    }])
                    ->get();
                break;
            case 'revenue':
                $reportData = Appointment::with(['service'])
                    ->whereBetween('appointment_date', [$request->date_from, $request->date_to])
                    ->where('status', '!=', 'cancelled')
                    ->get()
                    ->groupBy(function ($appointment) {
                        return $appointment->appointment_date->format('Y-m-d');
                    });
                break;
        }

        return view('admin.reports.result', compact('reportData', 'request'));
    }
    public function showScheduleDetails(Schedule $schedule): View
    {
        $schedule->load(['user', 'services']);
        $stats = $this->getScheduleStats($schedule);

        return view('admin.schedules.show', compact('schedule', 'stats'));
    }

    public function getScheduleDayView(Schedule $schedule, string $date)
    {
        $daySchedule = $schedule->getDaySchedule($date);
        $workingHours = $schedule->getWorkingHoursForDate($date);

        return response()->json([
            'schedule' => $daySchedule,
            'working_hours' => $workingHours,
            'doctor' => $schedule->user->name,
            'date' => Carbon::parse($date)->format('d.m.Y'),
            'is_working_day' => !empty($workingHours),
            'interval' => $schedule->appointment_interval,
            'unlimited' => $schedule->hasUnlimitedAppointments()
        ]);
    }

    // public function getAppointmentsByDay(Request $request)
    // {
    //     $request->validate([
    //         'date' => 'required|date',
    //         'schedule_id' => 'nullable|exists:schedules,id'
    //     ]);

    //     $date = $request->date;
    //     $scheduleId = $request->schedule_id;

    //     $query = Appointment::with(['schedule.user', 'services', 'registrar'])
    //         ->whereDate('appointment_date', $date);

    //     if ($scheduleId) {
    //         $query->where('schedule_id', $scheduleId);
    //     }

    //     $appointments = $query->orderBy('appointment_time')->get();

    //     // Группируем по графикам
    //     $appointmentsBySchedule = $appointments->groupBy('schedule_id');

    //     $schedules = Schedule::with('user')
    //         ->whereIn('id', $appointments->pluck('schedule_id')->unique())
    //         ->get()
    //         ->keyBy('id');

    //     return view('admin.appointments.day-view', compact(
    //         'appointments',
    //         'appointmentsBySchedule',
    //         'schedules',
    //         'date'
    //     ));
    // }

    public function getServicesTreeData()
    {
        $catalogs = Catalog::with(['subCatalogs.services' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        return response()->json($catalogs);
    }

    /**
     * Получить все записи на конкретную дату для графика
     */
    public function getAppointmentsForDay(Schedule $schedule, string $date)
    {
        $appointments = Appointment::where('schedule_id', $schedule->id)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->with(['service'])
            ->orderBy('appointment_time')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'client_name' => $appointment->client_name,
                    'patient_iin' => $appointment->patient_iin,
                    'client_phone' => $appointment->client_phone,
                    'appointment_time' => $appointment->appointment_time ? Carbon::parse($appointment->appointment_time)->format('H:i') : null,
                    'appointment_end_time' => $appointment->appointment_end_time ? Carbon::parse($appointment->appointment_end_time)->format('H:i') : null,
                    'service_name' => $appointment->service ? $appointment->service->name : null,
                    'status' => $appointment->status,
                ];
            });

        return response()->json($appointments);
    }

    /**
     * Удалить день из графика
     */
    public function removeScheduleDay(Schedule $schedule, Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->input('date');
        $dateCarbon = Carbon::parse($date);

        // Получаем все записи на этот день
        $appointments = Appointment::where('schedule_id', $schedule->id)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        // Проверяем, есть ли конкретная дата в schedule_dates
        $scheduleDate = $schedule->scheduleDates()->where('date', $date)->first();
        
        if ($scheduleDate) {
            // Удаляем конкретную дату из schedule_dates
            $scheduleDate->delete();
        } else {
            // Если нет конкретной даты, отключаем день недели
            $dayName = strtolower($dateCarbon->format('l'));
            
            // Проверяем, что день недели активен
            if ($schedule->{$dayName . '_active'}) {
                $schedule->update([$dayName . '_active' => false]);
            }
        }

        return response()->json([
            'success' => true,
            'appointments' => $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'client_name' => $appointment->client_name,
                    'appointment_time' => $appointment->appointment_time ? Carbon::parse($appointment->appointment_time)->format('H:i') : null,
                ];
            }),
            'message' => $appointments->count() > 0 
                ? "День удален. Найдено {$appointments->count()} записей для переноса."
                : "День успешно удален. Записей не найдено."
        ]);
    }

    /**
     * Перенести записи на другую дату/время
     */
    public function rescheduleAppointments(Request $request)
    {
        $request->validate([
            'appointments' => 'required|array',
            'appointments.*.id' => 'required|exists:appointments,id',
            'appointments.*.new_date' => 'required|date',
            'appointments.*.new_time' => 'nullable|date_format:H:i',
        ]);

        $appointments = $request->input('appointments');
        $errors = [];
        $success = [];

        DB::beginTransaction();

        try {
            foreach ($appointments as $appointmentData) {
                $appointment = Appointment::findOrFail($appointmentData['id']);
                $schedule = $appointment->schedule;
                $newDate = $appointmentData['new_date'];
                $newTime = $appointmentData['new_time'] ?? null;

                // Проверяем, что новая дата является рабочим днем
                if (!$schedule->isWorkingDate($newDate)) {
                    $errors[] = [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client_name,
                        'error' => 'Выбранная дата не является рабочим днем'
                    ];
                    continue;
                }

                // Проверяем, что дата входит в период действия графика
                if (!$newDate || !Carbon::parse($newDate)->between($schedule->start_date, $schedule->end_date)) {
                    $errors[] = [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client_name,
                        'error' => 'Выбранная дата не входит в период действия графика'
                    ];
                    continue;
                }

                // Для неограниченных записей время не обязательно
                if (!$schedule->hasUnlimitedAppointments()) {
                    if (empty($newTime)) {
                        $errors[] = [
                            'appointment_id' => $appointment->id,
                            'client_name' => $appointment->client_name,
                            'error' => 'Необходимо указать время для записи'
                        ];
                        continue;
                    }

                    // Проверяем доступность слота (исключаем саму переносимую запись)
                    if (!$schedule->isTimeSlotAvailable($newDate, $newTime, $appointment->id)) {
                        $errors[] = [
                            'appointment_id' => $appointment->id,
                            'client_name' => $appointment->client_name,
                            'error' => "Время {$newTime} на дату {$newDate} уже занято"
                        ];
                        continue;
                    }

                    // Обновляем время
                    $appointment->appointment_time = $newTime;
                    $appointment->appointment_end_time = Carbon::parse($newTime)
                        ->addMinutes($schedule->appointment_interval)
                        ->format('H:i');
                } else {
                    // Для неограниченных записей время может быть null
                    $appointment->appointment_time = $newTime;
                    $appointment->appointment_end_time = $newTime ? Carbon::parse($newTime)
                        ->addMinutes($schedule->appointment_interval ?? 30)
                        ->format('H:i') : null;
                }

                // Обновляем дату
                $appointment->appointment_date = $newDate;
                $appointment->save();

                $success[] = [
                    'appointment_id' => $appointment->id,
                    'client_name' => $appointment->client_name,
                    'new_date' => $newDate,
                    'new_time' => $newTime,
                ];
            }

            if (count($errors) > 0 && count($success) === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'errors' => $errors,
                    'message' => 'Не удалось перенести записи из-за ошибок'
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($success) . ' записей успешно перенесено',
                'successful' => $success,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при переносе записей: ' . $e->getMessage()
            ], 500);
        }
    }
}
