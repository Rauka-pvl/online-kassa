{{-- resources/views/admin/reports/index.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Отчеты</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Генерация отчетов</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.reports.generate') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="report_type" class="form-label">Тип отчета *</label>
                        <select class="form-select @error('report_type') is-invalid @enderror"
                                id="report_type" name="report_type" required>
                            <option value="">Выберите тип отчета</option>
                            <option value="appointments" {{ old('report_type') == 'appointments' ? 'selected' : '' }}>
                                Отчет по записям
                            </option>
                            <option value="services" {{ old('report_type') == 'services' ? 'selected' : '' }}>
                                Отчет по услугам
                            </option>
                            <option value="doctors" {{ old('report_type') == 'doctors' ? 'selected' : '' }}>
                                Отчет по врачам
                            </option>
                            <option value="revenue" {{ old('report_type') == 'revenue' ? 'selected' : '' }}>
                                Отчет по доходам
                            </option>
                        </select>
                        @error('report_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_from" class="form-label">Дата с *</label>
                            <input type="date" class="form-control @error('date_from') is-invalid @enderror"
                                   id="date_from" name="date_from" value="{{ old('date_from', now()->startOfMonth()->format('Y-m-d')) }}" required>
                            @error('date_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date_to" class="form-label">Дата по *</label>
                            <input type="date" class="form-control @error('date_to') is-invalid @enderror"
                                   id="date_to" name="date_to" value="{{ old('date_to', now()->format('Y-m-d')) }}" required>
                            @error('date_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Сгенерировать отчет
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Быстрая статистика</h5>
            </div>
            <div class="card-body">
                @php
                    $todayStats = [
                        'appointments_today' => App\Models\Appointment::whereDate('appointment_date', today())->count(),
                        'appointments_pending' => App\Models\Appointment::where('status', 'pending')->count(),
                        'appointments_this_month' => App\Models\Appointment::whereMonth('appointment_date', now()->month)->count(),
                        'revenue_this_month' => App\Models\Appointment::whereMonth('appointment_date', now()->month)
                            ->where('status', '!=', 'cancelled')
                            ->with('service')
                            ->get()
                            ->sum('service.price')
                    ];
                @endphp

                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-2">
                            <h4 class="text-primary">{{ $todayStats['appointments_today'] }}</h4>
                            <small class="text-muted">Записей сегодня</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-2">
                            <h4 class="text-warning">{{ $todayStats['appointments_pending'] }}</h4>
                            <small class="text-muted">Ожидающих</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h4 class="text-success">{{ $todayStats['appointments_this_month'] }}</h4>
                            <small class="text-muted">Записей в месяце</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h4 class="text-info">{{ number_format($todayStats['revenue_this_month'], 0, '.', ' ') }} ₸</h4>
                            <small class="text-muted">Доход за месяц</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
