{{-- resources/views/admin/reports/result.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Результат отчета</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.reports') }}" class="btn btn-outline-secondary">
            ← Назад к отчетам
        </a>
    </div>
</div>

<div class="alert alert-info">
    <strong>Тип отчета:</strong>
    @switch($request->report_type)
        @case('appointments') Отчет по записям @break
        @case('services') Отчет по услугам @break
        @case('doctors') Отчет по врачам @break
        @case('revenue') Отчет по доходам @break
    @endswitch
    <br>
    <strong>Период:</strong> {{ \Carbon\Carbon::parse($request->date_from)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($request->date_to)->format('d.m.Y') }}
</div>

@if($request->report_type == 'appointments')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Записи за период</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Пациент</th>
                            <th>Врач</th>
                            <th>Услуга</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $appointment)
                            <tr>
                                <td>{{ $appointment->formatted_date }}</td>
                                <td>{{ $appointment->client_name }}</td>
                                <td>{{ $appointment->schedule->user->name }}</td>
                                <td>{{ $appointment->service->name }}</td>
                                <td>{{ $appointment->service->formatted_price }}</td>
                                <td>{{ $appointment->status_in_russian }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Записи не найдены</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif($request->report_type == 'services')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Популярность услуг</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Услуга</th>
                            <th>Подкаталог</th>
                            <th>Количество записей</th>
                            <th>Цена</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $service)
                            <tr>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->subCatalog->name }}</td>
                                <td>{{ $service->appointments_count }}</td>
                                <td>{{ $service->formatted_price }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Данные не найдены</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif($request->report_type == 'doctors')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Активность врачей</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Врач</th>
                            <th>Специализация</th>
                            <th>Количество графиков</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $doctor)
                            <tr>
                                <td>{{ $doctor->name }}</td>
                                <td>{{ $doctor->specialization ?? '-' }}</td>
                                <td>{{ $doctor->schedules_count }}</td>
                                <td>
                                    @if($doctor->is_active)
                                        <span class="badge bg-success">Активен</span>
                                    @else
                                        <span class="badge bg-danger">Неактивен</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Данные не найдены</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif($request->report_type == 'revenue')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Доходы по дням</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Количество записей</th>
                            <th>Общая сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalRevenue = 0; @endphp
                        @forelse($reportData as $date => $appointments)
                            @php
                                $dayRevenue = $appointments->where('status', '!=', 'cancelled')->sum('service.price');
                                $totalRevenue += $dayRevenue;
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</td>
                                <td>{{ $appointments->count() }}</td>
                                <td><strong>{{ number_format($dayRevenue, 0, '.', ' ') }} ₸</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Данные не найдены</td>
                            </tr>
                        @endforelse
                        @if($reportData->count() > 0)
                            <tr class="table-success">
                                <td><strong>Итого:</strong></td>
                                <td><strong>{{ $reportData->flatten()->count() }}</strong></td>
                                <td><strong>{{ number_format($totalRevenue, 0, '.', ' ') }} ₸</strong></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
