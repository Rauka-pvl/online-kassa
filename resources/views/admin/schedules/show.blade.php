{{-- resources/views/admin/schedules/show.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">График работы: {{ $schedule->user->name }}</h1>
        <p class="text-muted mb-0">ID: {{ $schedule->id }} | Интервал: {{ $schedule->formatted_interval }}</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary me-2">
            ✏️ Редактировать
        </a>
        <a href="{{ route('admin.schedules') }}" class="btn btn-outline-secondary">
            ← Назад к графикам
        </a>
    </div>
</div>

<div class="row">
    <!-- Информация о графике -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Информация о графике</h5>
            </div>
            <div class="card-body">
                <p><strong>Врач:</strong> {{ $schedule->user->name }}</p>
                @if($schedule->user->specialization)
                    <p><strong>Специализация:</strong> {{ $schedule->user->specialization }}</p>
                @endif
                <p><strong>Интервал приема:</strong> {{ $schedule->formatted_interval }}</p>
                <p><strong>Статус:</strong>
                    @if($schedule->is_active)
                        <span class="badge bg-success">Активен</span>
                    @else
                        <span class="badge bg-danger">Неактивен</span>
                    @endif
                </p>

                <hr>

                <h6>Рабочие дни:</h6>
                @php $workingDays = $schedule->getWorkingDays(); @endphp
                @if(count($workingDays) > 0)
                    @foreach($workingDays as $day => $hours)
                        <div class="d-flex justify-content-between">
                            <span>{{ $schedule->getDayNameInRussian($day) }}:</span>
                            <span>{{ $hours['start'] }} - {{ $hours['end'] }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">Рабочие дни не установлены</p>
                @endif

                <hr>

                <h6>Услуги ({{ $schedule->services->count() }}):</h6>
                @if($schedule->services->count() > 0)
                    @foreach($schedule->services as $service)
                        <div class="d-flex justify-content-between">
                            <span>{{ $service->name }}</span>
                            <span class="text-success">{{ $service->formatted_price }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">Услуги не назначены</p>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Статистика</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $stats['total_slots_per_week'] }}</h4>
                        <small class="text-muted">Слотов в неделю</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $stats['booked_slots_this_week'] }}</h4>
                        <small class="text-muted">Записей на неделе</small>
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <h4 class="text-info">{{ $stats['average_daily_slots'] }}</h4>
                        <small class="text-muted">Среднее в день</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">{{ $stats['efficiency_percent'] }}%</h4>
                        <small class="text-muted">Загруженность</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Календарь и просмотр по дням -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Просмотр по дням</h5>
                    <input type="date" class="form-control" id="schedule_date"
                           value="{{ now()->format('Y-m-d') }}" style="width: auto;">
                </div>
            </div>
            <div class="card-body" id="day_schedule_content">
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p class="mt-2">Загрузка расписания...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleDateInput = document.getElementById('schedule_date');
    const dayScheduleContent = document.getElementById('day_schedule_content');

    function loadDaySchedule(date) {
        dayScheduleContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <p class="mt-2">Загрузка расписания...</p>
            </div>
        `;

        fetch(`/admin/schedules/{{ $schedule->id }}/day/${date}`)
            .then(response => response.json())
            .then(data => {
                if (!data.is_working_day) {
                    dayScheduleContent.innerHTML = `
                        <div class="alert alert-warning text-center">
                            <h5>🚫 Нерабочий день</h5>
                            <p class="mb-0">${data.doctor} не работает ${data.date}</p>
                        </div>
                    `;
                    return;
                }

                let html = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">${data.doctor} - ${data.date}</h6>
                        <small class="text-muted">
                            ${data.working_hours.start} - ${data.working_hours.end}
                            ${data.unlimited ? '(неограниченно)' : '(интервал: ' + data.interval + 'мин)'}
                        </small>
                    </div>
                `;

                if (data.schedule.length === 0) {
                    html += `
                        <div class="alert alert-info text-center">
                            <h6>📅 Свободный день</h6>
                            <p class="mb-0">На этот день записей нет</p>
                        </div>
                    `;
                } else {
                    html += '<div class="timeline">';

                    data.schedule.forEach(slot => {
                        const timeClass = slot.is_free ? 'border-success bg-light' : 'border-primary bg-white';
                        const icon = slot.is_free ? '🟢' : '👤';

                        html += `
                            <div class="d-flex align-items-center mb-2 p-2 border ${timeClass} rounded">
                                <div class="me-3">
                                    <span class="badge bg-primary">${slot.time}</span>
                                </div>
                                <div class="flex-grow-1">
                                    ${slot.is_free ?
                                        '<span class="text-success">Свободно</span>' :
                                        `
                                        <strong>${slot.appointment.client_name}</strong>
                                        <br>
                                        <small class="text-muted">
                                            ${slot.appointment.services_list}
                                            (${slot.appointment.formatted_total_price})
                                        </small>
                                        `
                                    }
                                </div>
                                <div>
                                    ${icon}
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                }

                dayScheduleContent.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                dayScheduleContent.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <h6>❌ Ошибка загрузки</h6>
                        <p class="mb-0">Не удалось загрузить расписание</p>
                    </div>
                `;
            });
    }

    scheduleDateInput.addEventListener('change', function() {
        loadDaySchedule(this.value);
    });

    // Загружаем расписание на сегодня
    loadDaySchedule(scheduleDateInput.value);
});
</script>
@endsection
