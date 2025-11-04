@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Панель управления</h1>
</div>
@if (Auth::user()->role == 1)
    <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Каталоги</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['catalogs_count'] }}</div>
                            </div>
                            <div class="col-auto">
                                <span style="font-size: 2rem;">📁</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Услуги</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['services_count'] }}</div>
                            </div>
                            <div class="col-auto">
                                <span style="font-size: 2rem;">🛠️</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stats-card info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Врачи</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['doctors_count'] }}</div>
                            </div>
                            <div class="col-auto">
                                <span style="font-size: 2rem;">👨‍⚕️</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Записи</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['appointments_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <span style="font-size: 2rem;">📋</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif


<div class="row mb-4">
    @if (Auth::user()->role == 1)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Подкаталоги</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['subcatalogs_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <span style="font-size: 2rem;">📂</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Регистраторы</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['registrars_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <span style="font-size: 2rem;">👥</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Графики</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['schedules_count'] }}</div>
                    </div>
                    <div class="col-auto">
                        <span style="font-size: 2rem;">📅</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Быстрые действия</h5>
            </div>
            <div class="card-body">
                <div class="row">

                    @if (Auth::user()->role == 1)
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.catalogs.create') }}" class="btn btn-primary w-100">
                                📁 Добавить каталог
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.services.create') }}" class="btn btn-success w-100">
                                🛠️ Добавить услугу
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-info w-100">
                                👥 Добавить пользователя
                            </a>
                        </div>
                    @endif

                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.schedules.create') }}" class="btn btn-warning w-100">
                            📅 Создать график
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Недавние записи</h5>
            </div>
            <div class="card-body">
                @php
                    // $recentAppointments = App\Models\Appointment::with(['service', 'schedule.user'])
                    //     ->orderBy('created_at', 'desc')
                    //     ->take(5)
                    //     ->get();
                @endphp

                {{-- @if($recentAppointments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentAppointments as $appointment)
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">{{ $appointment->client_name }}</div>
                                    <small>{{ $appointment->service->name }} - {{ $appointment->schedule->user->name }}</small>
                                    <br><small class="text-muted">{{ $appointment->formatted_date }}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $appointment->status_in_russian }}</span>
                            </div>
                        @endforeach
                    </div>
                @else --}}
                    <p class="text-muted">Записей пока нет</p>
                {{-- @endif --}}
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Активные врачи</h5>
            </div>
            <div class="card-body">
                @php
                    $activeDoctors = App\Models\User::where('role', 4)
                        ->where('is_active', true)
                        ->withCount('schedules')
                        ->take(5)
                        ->get();
                @endphp

                @if($activeDoctors->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($activeDoctors as $doctor)
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">{{ $doctor->name }}</div>
                                    <small>{{ $doctor->specialization ?? 'Специализация не указана' }}</small>
                                </div>
                                <span class="badge bg-success rounded-pill">{{ $doctor->schedules_count }} график(ов)</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">Активных врачей нет</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
