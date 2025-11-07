@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Записи пациентов</h1>
    </div>

    <!-- Панель фильтров -->
    {{-- <div class="card mb-3" id="filtersPanel">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Врач:</label>
                    <select class="form-select form-select-sm">
                        <option>Все</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group btn-group-sm w-100">
                        <button class="btn btn-primary">Поиск</button>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Панель поиска и фильтров -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.appointments') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Поиск по врачу:</label>
                    <input type="text" 
                           name="doctor_search" 
                           class="form-control form-control-sm" 
                           placeholder="Имя врача..." 
                           value="{{ request('doctor_search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Поиск по пациенту:</label>
                    <input type="text" 
                           name="patient_search" 
                           class="form-control form-control-sm" 
                           placeholder="ФИО, телефон или ИИН..." 
                           value="{{ request('patient_search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Фильтр по врачу:</label>
                    <select name="doctor_filter" class="form-select form-select-sm">
                        <option value="">Все врачи</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_filter') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Статус записи:</label>
                    <select name="status_filter" class="form-select form-select-sm">
                        <option value="">Все статусы</option>
                        <option value="pending" {{ request('status_filter') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                        <option value="confirmed" {{ request('status_filter') == 'confirmed' ? 'selected' : '' }}>Подтверждено</option>
                        <option value="completed" {{ request('status_filter') == 'completed' ? 'selected' : '' }}>Завершено</option>
                        <option value="cancelled" {{ request('status_filter') == 'cancelled' ? 'selected' : '' }}>Отменено</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Дата:</label>
                    <div class="input-group input-group-sm">
                        <button type="button" 
                                class="btn btn-outline-secondary" 
                                onclick="changeDate(-1)"
                                title="Предыдущий день">
                            ←
                        </button>
                        <input type="date" 
                               name="date" 
                               id="date_input"
                               class="form-control form-control-sm"
                               value="{{ request('date') ?? date('Y-m-d') }}"
                               onchange="this.form.submit()">
                        <button type="button" 
                                class="btn btn-outline-secondary" 
                                onclick="changeDate(1)"
                                title="Следующий день">
                            →
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Сортировка:</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>По ID</option>
                        <option value="doctor_name" {{ request('sort_by') == 'doctor_name' ? 'selected' : '' }}>По врачу</option>
                        <option value="appointment_date" {{ request('sort_by') == 'appointment_date' ? 'selected' : '' }}>По дате записи</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>По дате создания</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Порядок:</label>
                    <select name="sort_order" class="form-select form-select-sm">
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Применить</button>
                        @if(request()->anyFilled(['doctor_search', 'patient_search', 'doctor_filter', 'status_filter', 'date', 'sort_by', 'sort_order']))
                            <a href="{{ route('admin.appointments') }}" class="btn btn-outline-secondary btn-sm">Сбросить</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    @php
        use Carbon\Carbon;
        use Carbon\CarbonPeriod;

        Carbon::setLocale('ru'); // русская локализация

        $startDate = request()->get('date')
            ? \Carbon\Carbon::parse(request()->get('date'), 'Asia/Almaty')
            : now('Asia/Almaty');
        $endDate = $startDate->copy()->addDays(4);

        $period = CarbonPeriod::create($startDate, $endDate);

        $adminController = new App\Http\Controllers\Admin\AdminController();
        $showList = $showList ?? false;
    @endphp

    @if($showList)
        <!-- Список записей при поиске по пациенту -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Найденные записи</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Дата и время</th>
                                <th>Пациент</th>
                                <th>ИИН</th>
                                <th>Телефон</th>
                                <th>Врач</th>
                                <th>Услуга</th>
                                <th>Цена</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->id }}</td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d.m.Y') }}</div>
                                        @if($appointment->appointment_time)
                                            <small class="text-muted">{{ $appointment->appointment_time }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $appointment->client_name }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $appointment->patient_iin }}</small>
                                    </td>
                                    <td>
                                        <a href="tel:{{ $appointment->client_phone }}">{{ $appointment->client_phone }}</a>
                                    </td>
                                    <td>
                                        {{ $appointment->schedule->user->name }}
                                    </td>
                                    <td>
                                        {{ $appointment->service->name }}
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ number_format($appointment->total_price, 0, '.', ' ') }} ₸</strong>
                                    </td>
                                    <td>
                                        @if($appointment->status == 'pending')
                                            <span class="badge bg-warning">Ожидает</span>
                                        @elseif($appointment->status == 'confirmed')
                                            <span class="badge bg-success">Подтверждено</span>
                                        @elseif($appointment->status == 'completed')
                                            <span class="badge bg-info">Завершено</span>
                                        @elseif($appointment->status == 'cancelled')
                                            <span class="badge bg-danger">Отменено</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="confirmAppointment({{ $appointment->id }})" 
                                                    title="Подтвердить">
                                                ✅
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="editAppointment({{ $appointment->id }})" 
                                                    title="Редактировать">
                                                ✏️
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteAppointment({{ $appointment->id }})" 
                                                    title="Удалить">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <p>Записи не найдены</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($appointments->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $appointments->links() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Обычный режим - графики -->
    <div class="card">
        <div class="card-body p-0">
            <!-- Заголовки дней недели -->
            <div class="row g-0 border-bottom bg-light">
                <div class="col-2 p-2 border-end">
                    <strong>Врач</strong>
                </div>
                @foreach ($period as $date)
                    <div class="col-2 p-2 border-end text-center">
                        <div><strong>{{ $date->translatedFormat('D, d M') }}</strong></div>
                        @if ($date->isToday())
                            <div class="small text-success">Сегодня</div>
                        @elseif ($date->isTomorrow())
                            <div class="small text-primary">Завтра</div>
                        @elseif ($date->isYesterday())
                            <div class="small text-muted">Вчера</div>
                        @endif
                    </div>
                @endforeach
            </div>
            @foreach ($schedules as $schedule)
                <div class="row g-0 border-bottom schedule-row">
                    <div class="col-2 p-3 border-end bg-white">
                        <div class="fw-bold">{{ $schedule->user->name }}</div>
                        <div class="small text-muted">Кабинет {{ $schedule->room }}</div>
                    </div>
                    @foreach ($period as $date)
                        @php
                            $dayKey = strtolower($date->format('l')); // например, monday
                            $active = $schedule->{$dayKey . '_active'};
                            $start = $schedule->{$dayKey . '_start'};
                            $end = $schedule->{$dayKey . '_end'};
                        @endphp

                        <div class="col-2 p-2 border-end text-center schedule-cell
                                {{ $active && $date->between($schedule->start_date, $schedule->end_date, true) && $date >= Carbon::today() ? 'bg-success-subtle' : 'bg-light text-muted' }}"
                            @if (
                                $active &&
                                    $start &&
                                    $end &&
                                    $date->between($schedule->start_date, $schedule->end_date, true) &&
                                    $date >= Carbon::today()) onclick="openDoctorSchedule({{ $schedule->id }}, '{{ $schedule->user->name }}', '{{ $date->format('Y-m-d') }}')" @endif>
                            @if ($active && $start && $end && $date->between($schedule->start_date, $schedule->end_date, true))
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($start)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($end)->format('H:i') }}</div>
                                @if (!$schedule->appointment_interval && $schedule->unlimited_appointments)
                                    <div class="text-success fw-bold">
                                        Безлимитный приём
                                    </div>
                                @else
                                    @php
                                        $slots = $adminController->getTimeSlotsAdmin($schedule, $date->format('Y-m-d'));
                                        $slots = $slots->getData(true);
                                        $totalSlots = isset($slots['booked_slots']) ? count($slots['booked_slots']) : 0;
                                        $freeSlots = isset($slots['available_slots'])
                                            ? count($slots['available_slots'])
                                            : 0;
                                        $slotClass = 'text-success fw-bold';
                                        if ($freeSlots === 0) {
                                            $slotClass = 'bg-danger-subtle text-danger fw-bold p-1 rounded';
                                        } elseif ($totalSlots > 0 && $freeSlots <= $totalSlots / 2) {
                                            $slotClass = 'bg-warning-subtle text-warning fw-bold p-1 rounded';
                                        }
                                    @endphp
                                    <div class="{{ $slotClass }}">
                                        Свободно: {{ $freeSlots }} <br>
                                        Занято: {{ $totalSlots }}
                                    </div>
                                @endif
                            @else
                                <div class="small">Нет приёма</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <!-- Модальное окно детального просмотра времени врача -->
    <div class="modal fade" id="doctorScheduleModal" tabindex="-3">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="doctorModalTitle">Расписание врача</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <button
                                onclick="bootstrap.Modal.getInstance(document.getElementById('doctorScheduleModal')).hide();"
                                class="btn btn-outline-primary btn-sm">Вернуться к графикам</button>
                        </div>
                    </div>

                    <!-- Временные слоты -->
                    <div class="row">
                        <div class="col-12">
                            <div id="doctorSlotsContainer" class="time-slots-container"
                                style="max-height: 400px; overflow-y: auto;">
                                {{-- <div class="time-slot p-2 mb-2 bg-light border rounded" onclick="selectTimeSlot('')">
                                    <strong></strong>
                                    <div class="slot-controls mt-1">
                                        <button class="btn btn-sm btn-outline-primary">📅</button>
                                        <button class="btn btn-sm btn-outline-warning">👥</button>
                                        <button class="btn btn-sm btn-outline-info">📋</button>
                                    </div>
                                </div> --}}
                            </div>

                            {{-- <div class="mt-3">
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input" type="checkbox" id="showAll">
                                    <label class="form-check-label" for="showAll">
                                        Показывать все
                                    </label>
                                </div>
                            </div> --}}
                        </div>

                        {{-- <div class="col-4">
                            <div class="appointment-actions">
                                <button class="btn btn-success btn-sm mb-2 w-100">Свободно</button>
                                <button class="btn btn-danger btn-sm mb-2 w-100">Занято</button>
                                <button class="btn btn-secondary btn-sm mb-2 w-100">Забронировано</button>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Пагинация для графиков -->
        @if(isset($schedules) && $schedules->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">Отображаются графики с {{ $schedules->firstItem() }} по
                        {{ $schedules->lastItem() }}, всего {{ $schedules->total() }}</small>
                </div>
                <nav>
                    <ul class="pagination pagination-sm">
                        @if ($schedules->currentPage() > 1)
                            <li class="page-item">
                                <a href="{{ $schedules->url(1) }}" class="page-link">◀◀</a>
                            </li>
                            <li class="page-item">
                                <a href="{{ $schedules->previousPageUrl() }}" class="page-link">◀</a>
                            </li>
                        @endif

                        <li class="page-item active">
                            <span class="page-link">{{ $schedules->currentPage() }}</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">из {{ $schedules->lastPage() }}</span>
                        </li>
                        <li class="page-item">
                            <a href="{{ $schedules->nextPageUrl() }}" class="page-link">▶</a>
                        </li>
                        <li class="page-item">
                            <a href="{{ $schedules->url($schedules->lastPage()) }}" class="page-link">▶▶</a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    @endif

    <!-- Модальное окно записи пациента -->
    <div class="modal fade" id="appointmentModal" tabindex="-2">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Новая запись</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="appointmentForm" method="POST" action="">
                        @csrf
                        <input type="hidden" name="date" id="appointmentDate">
                        <input type="hidden" name="time" id="appointmentTime">

                        <div class="mb-3">
                            <label class="form-label">Врач</label>
                            <input type="text" class="form-control" id="appointmentDoctor" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Услуга</label>
                            <select name="service_id" id="appointmentService" class="form-select" required>
                                <option value="">Загрузка...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ФИО пациента</label>
                            <input type="text" class="form-control" id="patient_name" name="patient_name"
                                placeholder="Введите ФИО пациента" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ИИН пациента</label>
                            <input type="text" class="form-control" id="patient_iin" name="patient_iin"
                                placeholder="Введите ИИН пациента" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Телефон</label>
                            <input type="text" class="form-control" id="patient_phone" name="patient_phone"
                                placeholder="+7 (___) ___-__-__" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Статус</label>
                            <select class="form-control" name="status" id="status_appointment">
                                <option id="pending_status" value="pending">Ожидает</option>
                                <option id="confirmed_status" value="confirmed">Подтверждено</option>
                                {{-- <option id="canceled_status" value="canceled">Отменено</option>
                                <option id="completed_status" value="completed">Завершено</option> --}}
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Записать</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/imask"></script>
    <script>
        // Навигация по датам
        function changeDate(days) {
            const dateInput = document.getElementById('date_input');
            if (!dateInput) return;
            
            const currentDate = new Date(dateInput.value);
            currentDate.setDate(currentDate.getDate() + days);
            
            const year = currentDate.getFullYear();
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const day = String(currentDate.getDate()).padStart(2, '0');
            
            dateInput.value = `${year}-${month}-${day}`;
            dateInput.form.submit();
        }

        document.addEventListener("DOMContentLoaded", function() {
            var phoneInput = document.querySelector("input[name='patient_phone']");
            IMask(phoneInput, {
                mask: '+{7}(000)000-00-00'
            });
            var iinInput = document.querySelector("input[name='patient_iin']");
            IMask(iinInput, {
                mask: /^[0-9]{0,12}$/ // только цифры, максимум 12
            });
        });
    </script>

    <style>
        /* Навигация по датам */
        .input-group .btn {
            border-radius: 0;
            min-width: 40px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .input-group .btn:hover {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .input-group .form-control {
            border-left: none;
            border-right: none;
        }

        .input-group .btn:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group .btn:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Таблица записей */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table th {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            vertical-align: middle;
            font-size: 14px;
        }

        /* Графики */
        .schedule-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .schedule-row:hover {
            background-color: #f8f9fa;
        }

        .schedule-cell {
            border: 1px solid #dee2e6;
            min-height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .bg-success-subtle {
            background-color: #d1e7dd !important;
        }

        .bg-warning-subtle {
            background-color: #fff3cd !important;
        }

        .bg-danger-subtle {
            background-color: #f8d7da !important;
        }

        .time-slot {
            cursor: pointer;
            transition: all 0.2s;
        }

        .time-slot:hover {
            background-color: #e9ecef !important;
            transform: translateX(5px);
        }

        .slot-controls .btn {
            margin-right: 5px;
        }

        /* Кнопки действий */
        .btn-group-sm .btn {
            padding: 4px 8px;
            font-size: 12px;
        }
    </style>

    <script>
        function openDoctorSchedule(scheduleId, doctorName, date) {
            fetch(`/admin/schedules/${scheduleId}/day/${date}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('doctorSlotsContainer');
                    container.innerHTML = ''; // очистим прошлое содержимое

                    if (!data.is_working_day) {
                        container.innerHTML = `<div class="alert alert-warning">В этот день врач не работает</div>`;
                        return;
                    }
                    if (data.unlimited != true && data.interval != null) {
                        data.schedule.forEach(slot => {
                            // если слот свободен
                            let slotDiv = document.createElement('div');
                            slotDiv.className = "time-slot p-2 mb-2 border rounded " +
                                (slot.is_free ? "bg-light" : " text-black");

                            if (slot.appointment) {
                                if (slot.appointment.status == 'confirmed') {
                                    slotDiv.classList.add("bg-success-subtle");
                                }
                                slotDiv.innerHTML = `
                                    <strong>${slot.time} - ${slot.appointment.client_name}, ${slot.appointment.patient_iin}, ${slot.appointment.client_phone}</strong>
                                    <br>
                                    <strong>${slot.appointment.service.name} (${slot.appointment.service.price})</strong>
                                    <div class="slot-controls mt-1">
                                        <button class="btn btn-sm btn-outline-success" onclick="confirmAppointment('${slot.appointment.id}')">✅</button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="editAppointment('${slot.appointment.id}')">✏️</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAppointment('${slot.appointment.id}')">🗑️</button>
                                    </div>
                                `;
                            } else {
                                slotDiv.setAttribute("onclick",
                                    `selectTimeSlot('${slot.time}','${scheduleId}','${doctorName}','${date}')`
                                );
                                slotDiv.innerHTML = `
                                    <strong>${slot.time}</strong>
                                    {{-- <div class="slot-controls mt-1">
                                        <button class="btn btn-sm btn-outline-primary">📅</button>
                                        <button class="btn btn-sm btn-outline-warning">👥</button>
                                        <button class="btn btn-sm btn-outline-info">📋</button>
                                    </div> --}}
                                `;
                            }

                            container.appendChild(slotDiv);
                        });
                    } else if (data.unlimited == true && data.interval == null) {
                        container.innerHTML =
                            `<div class="alert alert-info">У врача безлимитный приём в этот день</div>`;
                    } else {
                        container.innerHTML =
                            `<div class="alert alert-info">У врача нет настроенного интервала приёма в этот день</div>`;
                    }

                    // показать модалку
                    new bootstrap.Modal(document.getElementById('doctorScheduleModal')).show();
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('doctorSlotsContainer').innerHTML =
                        `<div class="alert alert-danger">Ошибка загрузки расписания</div>`;
                });
        }

        function selectTimeSlot(timeSlot, scheduleId, doctorName, date) {
            const doctorModal = bootstrap.Modal.getInstance(document.getElementById('doctorScheduleModal'));
            if (doctorModal) doctorModal.hide();

            // Заполняем форму данными
            document.getElementById('appointmentDate').value = date;
            document.getElementById('appointmentTime').value = timeSlot;
            document.getElementById('appointmentDoctor').value = doctorName;

            document.getElementById('appointmentModalLabel').textContent =
                `Запись к врачу ${doctorName} на ${date} в ${timeSlot}`;
            document.getElementById('appointmentForm').action = `/admin/appointment/create/${scheduleId}`;
            // Загружаем список услуг для врача
            fetch(`/admin/schedules/${scheduleId}/services`)
                .then(res => res.json())
                .then(services => {
                    const serviceSelect = document.getElementById('appointmentService');
                    serviceSelect.innerHTML = ''; // очистим

                    if (services.length === 0) {
                        serviceSelect.innerHTML = `<option value="">Нет доступных услуг</option>`;
                    } else {
                        let defaultOpt = document.createElement('option');
                        defaultOpt.value = '';
                        defaultOpt.textContent = 'Выберите услугу';
                        defaultOpt.selected = true;
                        defaultOpt.disabled = true;
                        serviceSelect.appendChild(defaultOpt);
                        services.forEach(s => {
                            let opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.name + ' - ' + s.price;
                            serviceSelect.appendChild(opt);
                        });
                    }
                });

            // Проверим слот (свободен/занят)
            // fetch(`/admin/schedules/${scheduleId}/check-slot?date=${date}&time=${timeSlot}`)
            //     .then(res => res.json())
            //     .then(status => {
            //         if (!status.free) {
            //             alert(`Время ${timeSlot} уже занято!`);
            //         }
            //     });

            // Открыть модалку
            new bootstrap.Modal(document.getElementById('appointmentModal'), {
                backdrop: true,
                keyboard: true
            }).show();
        }


        function confirmAppointment(appointmentId) {
            fetch(`/admin/appointment/complete/${appointmentId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (response.ok) {
                        alert('Запись успешно подтверждена!');
                        // например, можно обновить таблицу
                        location.reload();
                    } else {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Ошибка при подтверждении записи');
                        });
                    }
                })
                .catch(error => {
                    alert('Ошибка: ' + error.message);
                });
        }

        function editAppointment(appointmentId) {
            // alert(`Редактирование записи ID: ${appointmentId}`);
            const doctorModal = bootstrap.Modal.getInstance(document.getElementById('doctorScheduleModal'));
            if (doctorModal) doctorModal.hide();


            document.getElementById('appointmentForm').action = `/admin/appointment/update/${appointmentId}`;

            // Загружаем список услуг для врача
            fetch(`/admin/appointment/get/${appointmentId}`)
                .then(res => res.json())
                .then(appointment => {
                    console.log(appointment);
                    document.getElementById('appointmentModalLabel').textContent =
                        `Редактирование записи к врачу ${appointment.schedule.user.name} на ${appointment.appointment_date.split('T')[0]} в ${appointment.appointment_time} - ${appointment.appointment_end_time}`;
                    document.getElementById('appointmentDate').value = appointment.appointment_date;
                    document.getElementById('appointmentTime').value = appointment.appointment_time
                    document.getElementById('appointmentDoctor').value = appointment.schedule.user.name;

                    document.getElementById('patient_iin').value = appointment.patient_iin;
                    document.getElementById('patient_name').value = appointment.client_name;
                    document.getElementById('patient_phone').value = appointment.client_phone;

                    document.getElementById(appointment.status + '_status').selected = true;

                    fetch(`/admin/schedules/${appointment.schedule.id}/services`)
                        .then(res => res.json())
                        .then(services => {
                            const serviceSelect = document.getElementById('appointmentService');
                            serviceSelect.innerHTML = ''; // очистим

                            if (services.length === 0) {
                                serviceSelect.innerHTML = `<option value="">Нет доступных услуг</option>`;
                            } else {
                                let defaultOpt = document.createElement('option');
                                defaultOpt.value = '';
                                defaultOpt.textContent = 'Выберите услугу';
                                defaultOpt.selected = true;
                                defaultOpt.disabled = true;
                                serviceSelect.appendChild(defaultOpt);
                                services.forEach(s => {
                                    let opt = document.createElement('option');
                                    opt.value = s.id;
                                    opt.textContent = s.name + ' - ' + s.price;
                                    if (s.id === appointment.service_id) {
                                        opt.selected = true;
                                    }
                                    serviceSelect.appendChild(opt);
                                });
                            }

                        });
                });

            // Открыть модалку
            new bootstrap.Modal(document.getElementById('appointmentModal'), {
                backdrop: true,
                keyboard: true
            }).show();

        }

        function deleteAppointment(appointmentId) {
            if (confirm('Вы уверены, что хотите удалить эту запись?')) {

                fetch(`/admin/appointment/delete/${appointmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            alert('Запись успешно удалена!');
                            // например, можно обновить таблицу
                            location.reload();
                        } else {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Ошибка при удалении');
                            });
                        }
                    })
                    .catch(error => {
                        alert('Ошибка: ' + error.message);
                    });
            }
        }
    </script>
@endsection
