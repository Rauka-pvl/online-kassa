{{-- resources/views/admin/schedules/index.blade.php (обновленная версия) --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Графики работы</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
            + Создать график
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Врач</th>
                        <th>Интервал приема</th>
                        <th>Рабочие дни</th>
                        <th>Услуги</th>
                        <th>Статус</th>
                        <th>Создан</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->id }}</td>
                            <td>
                                <strong>{{ $schedule->user->name }}</strong>
                                @if($schedule->user->specialization)
                                    <br><small class="text-muted">{{ $schedule->user->specialization }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $schedule->formatted_interval }}</span>
                                <br><small class="text-muted">@if($schedule->unlimited_appointments != true){{ $schedule->appointment_interval }} мин @else Безлимитный приём @endif</small>
                            </td>
                            <td>
                                @php
                                    $workingDays = $schedule->getWorkingDays();
                                    $dayNames = [
                                        'monday' => 'Пн',
                                        'tuesday' => 'Вт',
                                        'wednesday' => 'Ср',
                                        'thursday' => 'Чт',
                                        'friday' => 'Пт',
                                        'saturday' => 'Сб',
                                        'sunday' => 'Вс'
                                    ];
                                @endphp

                                @if(count($workingDays) > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($workingDays as $day => $hours)
                                            <span class="badge bg-success" title="{{ $hours['start'] }} - {{ $hours['end'] }}">
                                                {{ $dayNames[$day] }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block">
                                        @php
                                            if($schedule->unlimited_appointments == false) {
                                                $firstDay = collect($workingDays)->first();
                                                $totalHours = 0;
                                                $totalSlots = 0;
                                                foreach($workingDays as $dayHours) {
                                                    $start = \Carbon\Carbon::parse($dayHours['start']);
                                                    $end = \Carbon\Carbon::parse($dayHours['end']);
                                                    $dayMinutes = $end->diffInMinutes($start);
                                                    $totalHours += $dayMinutes / 60;
                                                    $totalSlots += floor($dayMinutes / $schedule->appointment_interval);
                                                }
                                            }

                                        @endphp
                                        @if ($schedule->unlimited_appointments)
                                            Безлимитный приём
                                        @else
                                            {{ round($totalHours, 1) }}ч/неделя ({{ $totalSlots }} слотов)
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">Нет рабочих дней</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $schedule->services->count() }}</span>
                                @if($schedule->services->count() > 0)
                                    <div class="small text-muted">
                                        {{ $schedule->services->pluck('name')->take(2)->join(', ') }}
                                        @if($schedule->services->count() > 2)
                                            и ещё {{ $schedule->services->count() - 2 }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($schedule->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-danger">Неактивен</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $schedule->created_at->format('d.m.Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                            onclick="showScheduleDetails({{ $schedule->id }})" title="Просмотр">
                                        👁️
                                    </button>
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}"
                                       class="btn btn-sm btn-outline-primary" title="Редактировать">
                                        ✏️
                                    </a>
                                    <form action="{{ route('admin.schedules.destroy', $schedule) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Вы уверены, что хотите удалить этот график?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <p>Графики не найдены</p>
                                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                                        Создать первый график
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
            <div class="d-flex justify-content-center">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Модальное окно для просмотра деталей графика -->
<div class="modal fade" id="scheduleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали графика</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="scheduleDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showScheduleDetails(scheduleId) {
    const modal = new bootstrap.Modal(document.getElementById('scheduleDetailsModal'));
    const content = document.getElementById('scheduleDetailsContent');

    modal.show();

    // Здесь можно загрузить детали графика через AJAX
    // Пока показываем заглушку
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert-info">
                <strong>График №${scheduleId}</strong><br>
                Здесь будет подробная информация о графике с расписанием по дням и временными слотами.
            </div>
        `;
    }, 500);
}
</script>
@endsection
