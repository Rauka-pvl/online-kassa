@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Регистратор - Запись пациентов</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#searchPatientModal">
            <i class="bi bi-search"></i> Поиск пациента
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickAppointmentModal">
            <i class="bi bi-plus-circle"></i> Быстрая запись
        </button>
    </div>
</div>

<!-- Фильтры и навигация -->
<div class="card mb-4 filters-card">
    <div class="card-body">
        <form method="GET" id="filtersForm">
            <div class="row align-items-end g-3">
                <div class="col-md-3">
                    <label class="form-label text-white">Период</label>
                    <div class="input-group">
                        <input type="date" name="date_from" class="form-control"
                               value="{{ request('date_from', $startOfWeek->format('Y-m-d')) }}">
                        <span class="input-group-text">—</span>
                        <input type="date" name="date_to" class="form-control"
                               value="{{ request('date_to', $endOfWeek->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-white">Врач</label>
                    <select name="doctor_id" class="form-select">
                        <option value="">Все врачи</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-white">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Все</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Свободные слоты</option>
                        <option value="busy" {{ request('status') == 'busy' ? 'selected' : '' }}>Занятые слоты</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-white">Интервал</label>
                    <select name="interval_type" class="form-select">
                        <option value="">Все</option>
                        <option value="with_interval" {{ request('interval_type') == 'with_interval' ? 'selected' : '' }}>С интервалом</option>
                        <option value="unlimited" {{ request('interval_type') == 'unlimited' ? 'selected' : '' }}>Безлимитные</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-light w-100">
                        <i class="bi bi-funnel"></i> Применить
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Навигация по неделям -->
<div class="week-navigation mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <div class="btn-group" role="group">
                <a href="?{{ http_build_query(array_merge(request()->all(), ['week' => $startOfWeek->copy()->subWeek()->format('Y-m-d')])) }}"
                   class="btn btn-outline-primary btn-week-nav">
                    <i class="bi bi-chevron-left"></i> Предыдущая неделя
                </a>
                <a href="?{{ http_build_query(array_merge(request()->except('week'))) }}"
                   class="btn btn-primary btn-week-nav">
                    Текущая неделя
                </a>
                <a href="?{{ http_build_query(array_merge(request()->all(), ['week' => $startOfWeek->copy()->addWeek()->format('Y-m-d')])) }}"
                   class="btn btn-outline-primary btn-week-nav">
                    Следующая неделя <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <h4 class="mb-0">{{ $startOfWeek->format('d.m.Y') }} - {{ $endOfWeek->format('d.m.Y') }}</h4>
            <small class="text-muted">{{ $startOfWeek->locale('ru')->isoFormat('MMMM YYYY') }}</small>
        </div>
    </div>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-widget">
            <div class="h3 mb-0">{{ $stats['total_schedules'] }}</div>
            <small>Активных графиков</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-widget" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <div class="h3 mb-0">{{ $stats['available_slots'] }}</div>
            <small>Свободных слотов</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-widget" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
            <div class="h3 mb-0">{{ $stats['occupied_slots'] }}</div>
            <small>Занятых слотов</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-widget" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
            <div class="h3 mb-0">{{ $stats['total_appointments'] }}</div>
            <small>Записей на неделю</small>
        </div>
    </div>
</div>

<!-- Основная таблица расписаний -->
<div class="row">
    @forelse($weekData as $scheduleId => $data)
        <div class="col-12 mb-4">
            <div class="schedule-card">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="doctor-avatar me-3">
                                    {{ substr($data['schedule']->user->name, 0, 2) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $data['schedule']->user->name }}</h6>
                                    <small class="opacity-75">
                                        @if($data['schedule']->appointment_interval)
                                            Интервал: {{ $data['schedule']->appointment_interval }} мин
                                        @else
                                            <i class="bi bi-infinity"></i> Без ограничений
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <small>Услуги: {{ $data['schedule']->services->count() }}</small>
                                @if($data['schedule']->services->count() > 0)
                                    <br><small class="opacity-75">
                                        {{ $data['schedule']->services->pluck('name')->take(2)->join(', ') }}
                                        @if($data['schedule']->services->count() > 2)
                                            и ещё {{ $data['schedule']->services->count() - 2 }}
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <button class="btn btn-outline-light btn-sm" onclick="toggleScheduleDetails({{ $scheduleId }})">
                                <i class="bi bi-chevron-down" id="chevron-{{ $scheduleId }}"></i> Детали
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="row g-0">
                        @foreach($data['days'] as $dateStr => $dayData)
                            <div class="col-md">
                                <div class="day-column">
                                    <div class="day-header">
                                        <div class="fw-bold">{{ $dayData['date']->format('D') }}</div>
                                        <div class="small">{{ $dayData['date']->format('d.m') }}</div>
                                    </div>

                                    <div class="p-2" style="min-height: 200px;">
                                        @if($dayData['is_active'])
                                            <div class="text-center mb-2">
                                                <small class="text-muted">
                                                    {{ Carbon\Carbon::parse($dayData['start_time'])->format('H:i') }} -
                                                    {{ Carbon\Carbon::parse($dayData['end_time'])->format('H:i') }}
                                                </small>
                                            </div>

                                            @if($dayData['appointments_count'] > 0 || !$data['schedule']->appointment_interval)
                                                @if($data['schedule']->appointment_interval)
                                                    <!-- График с интервалами -->
                                                    @php
                                                        $timeSlots = $this->generateTimeSlots(
                                                            $dayData['start_time'],
                                                            $dayData['end_time'],
                                                            $data['schedule']->appointment_interval,
                                                            $dayData['appointments'],
                                                            $data['schedule']->unlimited_appointments
                                                        );
                                                    @endphp

                                                    @foreach($timeSlots as $slot)
                                                        <div class="time-slot {{ $slot['available'] ? 'available' : 'occupied' }} {{ $slot['unlimited'] ? 'unlimited' : '' }}"
                                                             onclick="openAppointmentModal('{{ $scheduleId }}', '{{ $dateStr }}', '{{ $slot['time'] }}')">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <strong>{{ $slot['time'] }}</strong>
                                                                @if(count($slot['appointments']) > 0)
                                                                    <span class="patient-count">{{ count($slot['appointments']) }}</span>
                                                                @endif
                                                            </div>

                                                            @foreach($slot['appointments'] as $appointment)
                                                                <div class="appointment-info">
                                                                    <div class="fw-bold">{{ $appointment->client_name }}</div>
                                                                    <div class="small">{{ $appointment->client_phone }}</div>
                                                                    <span class="badge {{ $appointment->status_badge }}">{{ $appointment->status_text }}</span>
                                                                </div>
                                                            @endforeach

                                                            @if($slot['available'])
                                                                <div class="text-center mt-2">
                                                                    <i class="bi bi-plus-circle"></i> Записать
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <!-- График без интервалов -->
                                                    <div class="time-slot unlimited"
                                                         onclick="openAppointmentModal('{{ $scheduleId }}', '{{ $dateStr }}', 'any')">
                                                        <div class="text-center">
                                                            <i class="bi bi-infinity"></i>
                                                            <div class="fw-bold">Любое время</div>
                                                            <small>Безлимитная запись</small>
                                                        </div>
                                                    </div>

                                                    @foreach($dayData['appointments'] as $appointment)
                                                        <div class="appointment-info mb-2">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <div class="fw-bold">{{ $appointment->client_name }}</div>
                                                                    <div class="small text-muted">{{ $appointment->client_phone }}</div>
                                                                    @if($appointment->appointment_time)
                                                                        <div class="small">{{ $appointment->formatted_time }}</div>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    <span class="badge {{ $appointment->status_badge }}">{{ $appointment->status_text }}</span>
                                                                    <div class="btn-group btn-group-sm mt-1">
                                                                        <button class="btn btn-outline-primary btn-sm"
                                                                                onclick="editAppointment({{ $appointment->id }})">
                                                                            <i class="bi bi-pencil"></i>
                                                                        </button>
                                                                        <button class="btn btn-outline-danger btn-sm"
                                                                                onclick="deleteAppointment({{ $appointment->id }})">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @else
                                                <div class="text-center text-muted py-4">
                                                    <i class="bi bi-calendar-plus"></i>
                                                    <div>Нет записей</div>
                                                    <button class="btn btn-sm btn-outline-primary mt-2"
                                                            onclick="openAppointmentModal('{{ $scheduleId }}', '{{ $dateStr }}', '{{ $data['schedule']->appointment_interval ? Carbon\Carbon::parse($dayData['start_time'])->format('H:i') : 'any' }}')">
                                                        Записать пациента
                                                    </button>
                                                </div>
                                            @endif
                                        @else
                                            <div class="working-status">
                                                <i class="bi bi-x-circle text-muted"></i>
                                                <div>Выходной день</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i>
                <h5>Нет активных графиков</h5>
                <p>В выбранный период нет активных графиков работы врачей.</p>
                <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                    Создать график работы
                </a>
            </div>
        </div>
    @endforelse
</div>

<!-- Модальное окно поиска пациента -->
<div class="modal fade" id="searchPatientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Поиск пациента</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Поиск по имени или телефону</label>
                    <input type="text" class="form-control" id="patientSearch" placeholder="Введите имя или телефон">
                </div>
                <div id="searchResults">
                    <!-- Результаты поиска будут здесь -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно быстрой записи -->
<div class="modal fade" id="quickAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Быстрая запись пациента</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickAppointmentForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Врач</label>
                                <select name="schedule_id" class="form-select" required>
                                    <option value="">Выберите врача</option>
                                    @foreach($weekData as $scheduleId => $data)
                                        <option value="{{ $scheduleId }}">{{ $data['schedule']->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Дата</label>
                                <input type="date" name="appointment_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Время</label>
                                <input type="time" name="appointment_time" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ФИО пациента</label>
                                <input type="text" name="client_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Телефон</label>
                                <input type="tel" name="client_phone" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="client_email" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Примечания</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="submitQuickAppointment()">
                    Создать запись
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно записи на конкретное время -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Запись на прием</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentModalContent">
                <!-- Содержимое будет загружено через AJAX -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .filters-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }

    .filters-card .form-control,
    .filters-card .form-select {
        background: rgba(255,255,255,0.9);
        border: none;
    }

    .schedule-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .schedule-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,.15);
    }

    .doctor-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .day-column {
        min-height: 300px;
        border-right: 1px solid #dee2e6;
    }

    .day-column:last-child {
        border-right: none;
    }

    .day-header {
        background: rgba(0,123,255,0.1);
        padding: 12px;
        text-align: center;
        font-weight: 600;
        border-bottom: 1px solid #dee2e6;
    }

    .time-slot {
        margin: 6px 8px;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        position: relative;
    }

    .time-slot.available {
        background: #d4edda;
        border-color: #28a745;
        color: #155724;
    }

    .time-slot.available:hover {
        background: #c3e6cb;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
    }

    .time-slot.occupied {
        background: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
        cursor: default;
    }

    .time-slot.unlimited {
        background: #fff3cd;
        border-color: #ffc107;
        color: #856404;
    }

    .time-slot.unlimited:hover {
        background: #ffeaa7;
        transform: translateY(-1px);
    }

    .appointment-info {
        background: rgba(255,255,255,0.8);
        padding: 6px;
        border-radius: 4px;
        margin-top: 6px;
        font-size: 0.85rem;
    }

    .patient-count {
        background: rgba(0,0,0,0.7);
        color: white;
        border-radius: 10px;
        padding: 2px 6px;
        font-size: 0.75rem;
        font-weight: bold;
    }

    .working-status {
        padding: 20px;
        text-align: center;
        color: #6c757d;
    }

    .stats-widget {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,.1);
    }

    .week-navigation {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,.05);
    }

    .btn-week-nav {
        border-radius: 20px;
        padding: 8px 16px;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .day-column {
            min-height: 200px;
            border-right: none;
            border-bottom: 1px solid #dee2e6;
        }

        .stats-widget {
            margin-bottom: 15px;
        }

        .btn-group .btn {
            font-size: 0.875rem;
            padding: 6px 12px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Автоматическая отправка формы фильтров при изменении
    document.querySelectorAll('#filtersForm select, #filtersForm input').forEach(element => {
        element.addEventListener('change', function() {
            document.getElementById('filtersForm').submit();
        });
    });

    // Поиск пациентов
    let searchTimeout;
    document.getElementById('patientSearch').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            document.getElementById('searchResults').innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            searchPatients(query);
        }, 300);
    });
});

function toggleScheduleDetails(scheduleId) {
    // Функция для разворачивания/сворачивания деталей графика
    const chevron = document.getElementById(`chevron-${scheduleId}`);
    if (chevron.classList.contains('bi-chevron-down')) {
        chevron.classList.remove('bi-chevron-down');
        chevron.classList.add('bi-chevron-up');
    } else {
        chevron.classList.remove('bi-chevron-up');
        chevron.classList.add('bi-chevron-down');
    }
}

function openAppointmentModal(scheduleId, date, time) {
    const modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
    const content = document.getElementById('appointmentModalContent');

    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
        </div>
    `;

    modal.show();

    // Загружаем форму записи
    const params = new URLSearchParams({
        schedule_id: scheduleId,
        date: date,
        time: time
    });

    fetch(`{{ route('admin.appointments.create') }}?${params.toString()}`)
        .then(response => response.text())
        .then(html => {
            // Извлекаем содержимое формы из полученного HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const formContent = doc.querySelector('.row.justify-content-center');

            if (formContent) {
                content.innerHTML = formContent.innerHTML;
                // Инициализируем скрипты формы
                initializeAppointmentForm();
            } else {
                content.innerHTML = '<div class="alert alert-danger">Ошибка загрузки формы</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Ошибка загрузки данных</div>';
        });
}

function searchPatients(query) {
    const resultsContainer = document.getElementById('searchResults');
    resultsContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div></div>';

    // Имитация поиска (здесь нужно реализовать реальный поиск через API)
    setTimeout(() => {
        resultsContainer.innerHTML = `
            <div class="list-group">
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Иванов Иван Иванович</h6>
                        <small>+7 (777) 123-45-67</small>
                    </div>
                    <p class="mb-1">ivan@example.com</p>
                    <button class="btn btn-sm btn-primary" onclick="selectPatient('Иванов Иван Иванович', '+7 (777) 123-45-67', 'ivan@example.com')">
                        Выбрать
                    </button>
                </div>
            </div>
        `;
    }, 500);
}

function selectPatient(name, phone, email) {
    // Заполняем форму быстрой записи данными пациента
    document.querySelector('#quickAppointmentForm [name="client_name"]').value = name;
    document.querySelector('#quickAppointmentForm [name="client_phone"]').value = phone;
    document.querySelector('#quickAppointmentForm [name="client_email"]').value = email;

    // Закрываем модальное окно поиска
    bootstrap.Modal.getInstance(document.getElementById('searchPatientModal')).hide();

    // Открываем модальное окно быстрой записи
    new bootstrap.Modal(document.getElementById('quickAppointmentModal')).show();
}

function submitQuickAppointment() {
    const form = document.getElementById('quickAppointmentForm');
    const formData = new FormData(form);

    // Валидация
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    // Отправляем данные
    fetch('{{ route("admin.appointments.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Закрываем модальное окно
            bootstrap.Modal.getInstance(document.getElementById('quickAppointmentModal')).hide();
            // Перезагружаем страницу или обновляем данные
            location.reload();
        } else {
            alert('Ошибка при создании записи: ' + (data.message || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при отправке данных');
    });
}

function editAppointment(appointmentId) {
    window.location.href = `{{ route('admin.appointments.edit', '') }}/${appointmentId}`;
}

function deleteAppointment(appointmentId) {
    if (confirm('Вы уверены, что хотите удалить эту запись?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.appointments.destroy', '') }}/${appointmentId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function initializeAppointmentForm() {
    // Инициализация маски телефона
    const phoneInput = document.querySelector('[name="client_phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('7')) {
                value = value.substring(1);
            }

            let formattedValue = '+7';
            if (value.length > 0) {
                formattedValue += ' (' + value.substring(0, 3);
            }
            if (value.length >= 4) {
                formattedValue += ') ' + value.substring(3, 6);
            }
            if (value.length >= 7) {
                formattedValue += '-' + value.substring(6, 8);
            }
            if (value.length >= 9) {
                formattedValue += '-' + value.substring(8, 10);
            }

            e.target.value = formattedValue;
        });
    }

    // Инициализация древовидного списка услуг
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    if (serviceCheckboxes.length > 0) {
        serviceCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateServiceCounters);
        });
        updateServiceCounters();
    }
}

function updateServiceCounters() {
    const checkboxes = document.querySelectorAll('.service-checkbox');
    let selectedCount = 0;
    let totalPrice = 0;

    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selectedCount++;
            totalPrice += parseFloat(checkbox.dataset.price || 0);
        }
    });

    const countElement = document.getElementById('selectedServicesCount');
    const priceElement = document.getElementById('totalPrice');

    if (countElement) countElement.textContent = selectedCount;
    if (priceElement) priceElement.textContent = totalPrice.toLocaleString('ru-RU');
}
</script>
@endpush
