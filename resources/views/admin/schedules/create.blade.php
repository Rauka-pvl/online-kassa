{{-- resources/views/admin/schedules/create.blade.php (обновленная версия с ручным вводом интервала) --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Создать график работы</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.schedules') }}" class="btn btn-outline-secondary">
            ← Назад к графикам
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.schedules.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="user_id" class="form-label">Врач *</label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                <option value="">Выберите врача</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('user_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }} - {{ $doctor->specialization ?? 'Специализация не указана' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Тип записи *</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="appointment_type"
                                       id="limited_appointments" value="limited"
                                       {{ old('appointment_type', 'limited') == 'limited' ? 'checked' : '' }}
                                       onchange="toggleAppointmentType()">
                                <label class="form-check-label" for="limited_appointments">
                                    <strong>С временными интервалами</strong>
                                    <br><small class="text-muted">Ограниченное количество записей с четким временем</small>
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="appointment_type"
                                       id="unlimited_appointments" value="unlimited"
                                       {{ old('appointment_type') == 'unlimited' ? 'checked' : '' }}
                                       onchange="toggleAppointmentType()">
                                <label class="form-check-label" for="unlimited_appointments">
                                    <strong>Неограниченные записи</strong>
                                    <br><small class="text-muted">Любое количество записей на любое время</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3" id="interval_section">
                            <label for="appointment_interval" class="form-label">Интервал приема (минуты)</label>
                            <div class="input-group">
                                <select class="form-select @error('appointment_interval') is-invalid @enderror"
                                        id="appointment_interval_select" onchange="updateIntervalInput()">
                                    <option value="">Выберите или введите</option>
                                    <option value="5" {{ old('appointment_interval') == '5' ? 'selected' : '' }}>5 минут</option>
                                    <option value="10" {{ old('appointment_interval') == '10' ? 'selected' : '' }}>10 минут</option>
                                    <option value="15" {{ old('appointment_interval') == '15' ? 'selected' : '' }}>15 минут</option>
                                    <option value="20" {{ old('appointment_interval') == '20' ? 'selected' : '' }}>20 минут</option>
                                    <option value="30" {{ old('appointment_interval', '30') == '30' ? 'selected' : '' }}>30 минут</option>
                                    <option value="45" {{ old('appointment_interval') == '45' ? 'selected' : '' }}>45 минут</option>
                                    <option value="60" {{ old('appointment_interval') == '60' ? 'selected' : '' }}>60 минут</option>
                                    <option value="90" {{ old('appointment_interval') == '90' ? 'selected' : '' }}>90 минут</option>
                                    <option value="120" {{ old('appointment_interval') == '120' ? 'selected' : '' }}>120 минут</option>
                                    <option value="custom">Ввести вручную</option>
                                </select>
                                <input type="number" class="form-control @error('appointment_interval') is-invalid @enderror"
                                       id="appointment_interval" name="appointment_interval"
                                       value="{{ old('appointment_interval', '30') }}"
                                       min="5" max="480" step="5"
                                       placeholder="Введите минуты" style="display: none;">
                            </div>
                            @error('appointment_interval')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">От 5 до 480 минут (8 часов)</div>

                            <!-- Скрытое поле для неограниченных записей -->
                            <input type="hidden" id="unlimited_appointments_flag" name="unlimited_appointments" value="0">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    График активен
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Рабочие дни и время</h5>
                        <div class="alert alert-info">
                            <strong>💡 Совет:</strong> Если галочка активна, обязательно укажите время начала и окончания работы.
                            Справа будет показано количество доступных слотов.
                        </div>

                        <div class="row">
                            @php
                                $days = [
                                    'monday' => 'Понедельник',
                                    'tuesday' => 'Вторник',
                                    'wednesday' => 'Среда',
                                    'thursday' => 'Четверг',
                                    'friday' => 'Пятница',
                                    'saturday' => 'Суббота',
                                    'sunday' => 'Воскресенье'
                                ];
                            @endphp

                            @foreach($days as $dayKey => $dayName)
                                <div class="col-md-12 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input day-checkbox" type="checkbox"
                                                               id="{{ $dayKey }}_active" name="{{ $dayKey }}_active" value="1"
                                                               {{ old($dayKey . '_active') ? 'checked' : '' }}
                                                               onchange="toggleTimeInputs('{{ $dayKey }}')">
                                                        <label class="form-check-label fw-bold" for="{{ $dayKey }}_active">
                                                            {{ $dayName }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="{{ $dayKey }}_start" class="form-label">Начало</label>
                                                    <input type="time" class="form-control time-input"
                                                           id="{{ $dayKey }}_start" name="{{ $dayKey }}_start"
                                                           value="{{ old($dayKey . '_start', '09:00') }}"
                                                           {{ old($dayKey . '_active') ? '' : 'disabled' }}>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="{{ $dayKey }}_end" class="form-label">Окончание</label>
                                                    <input type="time" class="form-control time-input"
                                                           id="{{ $dayKey }}_end" name="{{ $dayKey }}_end"
                                                           value="{{ old($dayKey . '_end', '18:00') }}"
                                                           {{ old($dayKey . '_active') ? '' : 'disabled' }}>
                                                </div>
                                                <div class="col-md-4">
                                                    <div id="{{ $dayKey }}_slots_info" class="slots-info">
                                                        <!-- Количество слотов будет показано здесь -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-4">
                        <h5>Период действия графика *</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Дата начала</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                       id="start_date" name="start_date"
                                       value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Дата окончания</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                       id="end_date" name="end_date"
                                       value="{{ old('end_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-text">График будет действовать в указанный период, включая начальную и конечную даты.</div>
                    </div>

                    <div class="mb-4">
                        <h5>Конкретные даты (опционально)</h5>
                        <div class="alert alert-info">
                            <strong>💡 Совет:</strong> Вы можете указать конкретные даты с индивидуальным временем работы. 
                            Эти даты будут иметь приоритет над днями недели.
                        </div>
                        <div id="schedule-dates-container">
                            <div class="schedule-date-item mb-3 p-3 border rounded">
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">Дата</label>
                                        <input type="date" class="form-control" name="schedule_dates[0][date]">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Начало</label>
                                        <input type="time" class="form-control" name="schedule_dates[0][start_time]" value="09:00">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Окончание</label>
                                        <input type="time" class="form-control" name="schedule_dates[0][end_time]" value="18:00">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="schedule_dates[0][is_active]" value="1" checked>
                                            <label class="form-check-label">Активна</label>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeScheduleDate(this)">×</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addScheduleDate()">
                            + Добавить дату
                        </button>
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-3">Услуги врача *</h5>
                        <div class="alert alert-info border-0 shadow-sm mb-4" id="services_notice" role="alert">
                            <small><i class="bi bi-info-circle me-1"></i> Все выбранные услуги будут доступны в рамках установленного интервала времени.</small>
                        </div>
                        <div class="services-tree">
                            @foreach($catalogs as $catalog)
                                <div class="catalog-item mb-2">
                                    <div class="catalog-header" onclick="toggleCatalogCollapse({{ $catalog->id }})">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="form-check mb-0 flex-grow-1">
                                                <input class="form-check-input catalog-checkbox"
                                                       type="checkbox"
                                                       id="catalog_{{ $catalog->id }}"
                                                       onclick="event.stopPropagation();"
                                                       onchange="toggleCatalogServices({{ $catalog->id }})">
                                                <label class="form-check-label fw-medium catalog-label" data-catalog-id="{{ $catalog->id }}">
                                                    {{ $catalog->name }}
                                                    <span class="badge bg-secondary-subtle text-secondary ms-2" id="catalog_count_{{ $catalog->id }}">0</span>
                                                </label>
                                            </div>
                                            <i class="bi bi-chevron-down catalog-icon" id="catalog_icon_{{ $catalog->id }}"></i>
                                        </div>
                                    </div>
                                    <div class="catalog-content" id="catalog_content_{{ $catalog->id }}">
                                        @foreach($catalog->subCatalogs as $subCatalog)
                                            <div class="subcatalog-item">
                                                <div class="subcatalog-header" onclick="toggleSubCatalogCollapse({{ $subCatalog->id }})">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="form-check mb-0 flex-grow-1">
                                                            <input class="form-check-input subcatalog-checkbox"
                                                                   type="checkbox"
                                                                   id="subcatalog_{{ $subCatalog->id }}"
                                                                   data-catalog="{{ $catalog->id }}"
                                                                   onclick="event.stopPropagation();"
                                                                   onchange="toggleSubCatalogServices({{ $subCatalog->id }}, {{ $catalog->id }})">
                                                            <label class="form-check-label fw-normal subcatalog-label" data-subcatalog-id="{{ $subCatalog->id }}">
                                                                {{ $subCatalog->name }}
                                                                <span class="badge bg-light text-dark ms-2" id="subcatalog_count_{{ $subCatalog->id }}">0</span>
                                                            </label>
                                                        </div>
                                                        <i class="bi bi-chevron-right subcatalog-icon" id="subcatalog_icon_{{ $subCatalog->id }}"></i>
                                                    </div>
                                                </div>
                                                <div class="subcatalog-content" id="subcatalog_content_{{ $subCatalog->id }}">
                                                    @foreach($subCatalog->services as $service)
                                                        <div class="service-item">
                                                            <div class="form-check">
                                                                <input class="form-check-input service-checkbox @error('services') is-invalid @enderror"
                                                                       type="checkbox"
                                                                       id="service_{{ $subCatalog->id }}_{{ $service->id }}"
                                                                       name="services[]"
                                                                       value="{{ $service->id }}"
                                                                       data-subcatalog="{{ $subCatalog->id }}"
                                                                       data-catalog="{{ $catalog->id }}"
                                                                       data-service="{{ $service->id }}"
                                                                       {{ in_array($service->id, old('services', [])) ? 'checked' : '' }}
                                                                       onchange="updateSubCatalogCheckbox({{ $subCatalog->id }}, {{ $catalog->id }}); syncServiceDuplicates(this)">
                                                                <label class="form-check-label" for="service_{{ $subCatalog->id }}_{{ $service->id }}">
                                                                    <span class="service-name">{{ $service->name }}</span>
                                                                    <span class="service-info">
                                                                        {{ $service->formatted_price }}
                                                                        @if($service->duration)
                                                                            <span class="text-muted">· {{ $service->duration }} мин</span>
                                                                        @endif
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('services')
                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.schedules') }}" class="btn btn-outline-secondary">
                            Отмена
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Создать график
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Slots info styles */
.slots-info {
    padding: 8px 12px;
    background-color: #f8f9fa;
    border-radius: 8px;
    min-height: 40px;
    display: flex;
    align-items: center;
    font-size: 13px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.slots-info.success {
    background-color: #d1e7dd;
    color: #0f5132;
    border-color: #badbcc;
}

.slots-info.warning {
    background-color: #fff3cd;
    color: #664d03;
    border-color: #ffecb5;
}

.slots-info.info {
    background-color: #cfe2ff;
    color: #084298;
    border-color: #b6d4fe;
}

/* Services tree styles - Minimalist */
.services-tree {
    background: #fff;
    border-radius: 12px;
    padding: 0;
}

.catalog-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    transition: all 0.2s ease;
}

.catalog-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.catalog-header {
    padding: 14px 16px;
    background: #f8f9fa;
    cursor: pointer;
    user-select: none;
    transition: background 0.2s ease;
    border-bottom: 1px solid #e9ecef;
}

.catalog-header:hover {
    background: #e9ecef;
}

.catalog-content {
    padding: 12px;
    display: none;
}

.catalog-content.show {
    display: block;
}

.catalog-icon {
    transition: transform 0.3s ease;
    color: #6c757d;
    font-size: 14px;
}

.catalog-icon.rotated {
    transform: rotate(180deg);
}

.subcatalog-item {
    margin-bottom: 8px;
    border-left: 2px solid #dee2e6;
    padding-left: 12px;
}

.subcatalog-header {
    padding: 10px 12px;
    cursor: pointer;
    user-select: none;
    transition: background 0.2s ease;
    border-radius: 6px;
    margin-bottom: 4px;
}

.subcatalog-header:hover {
    background: #f8f9fa;
}

.subcatalog-content {
    padding-left: 20px;
    display: none;
}

.subcatalog-content.show {
    display: block;
}

.subcatalog-icon {
    transition: transform 0.3s ease;
    color: #6c757d;
    font-size: 12px;
}

.subcatalog-icon.rotated {
    transform: rotate(90deg);
}

.service-item {
    padding: 8px 12px;
    margin-bottom: 4px;
    border-radius: 6px;
    transition: background 0.2s ease;
}

.service-item:hover {
    background: #f8f9fa;
}

.service-item .form-check-label {
    display: flex;
    flex-direction: column;
    cursor: pointer;
    width: 100%;
}

.service-name {
    font-weight: 500;
    color: #212529;
    font-size: 14px;
    margin-bottom: 2px;
}

.service-info {
    font-size: 12px;
    color: #6c757d;
}

/* Checkbox styles */
.form-check-input {
    width: 18px;
    height: 18px;
    margin-top: 0.25rem;
    cursor: pointer;
    border: 2px solid #dee2e6;
    transition: all 0.2s ease;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.form-check-input:indeterminate {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-label {
    cursor: pointer;
    user-select: none;
    margin-left: 8px;
}

.badge {
    font-size: 11px;
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 12px;
}

/* Form improvements */
.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-control, .form-select {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
}

.btn-primary {
    background: #0d6efd;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
}

.btn-primary:hover {
    background: #0b5ed7;
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
    transform: translateY(-1px);
}

.btn-outline-secondary {
    border: 1px solid #dee2e6;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.alert {
    border-radius: 8px;
    padding: 12px 16px;
}
</style>

<script>
function toggleAppointmentType() {
    const isLimited = document.getElementById('limited_appointments').checked;
    const intervalSection = document.getElementById('interval_section');
    const unlimitedFlag = document.getElementById('unlimited_appointments_flag');
    const servicesNotice = document.getElementById('services_notice');

    if (isLimited) {
        intervalSection.style.display = 'block';
        unlimitedFlag.value = '0';
        servicesNotice.innerHTML = '<strong>⚠️ Важно:</strong> Все выбранные услуги будут доступны в рамках установленного интервала времени.';
    } else {
        intervalSection.style.display = 'none';
        unlimitedFlag.value = '1';
        servicesNotice.innerHTML = '<strong>ℹ️ Информация:</strong> При неограниченных записях можно записать любое количество пациентов на любое время.';
    }

    // Пересчитываем слоты для всех дней
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    days.forEach(day => {
        if (document.getElementById(day + '_active').checked) {
            calculateSlots(day);
        }
    });
}

function updateIntervalInput() {
    const select = document.getElementById('appointment_interval_select');
    const input = document.getElementById('appointment_interval');

    if (select.value === 'custom') {
        select.style.display = 'none';
        input.style.display = 'block';
        input.focus();
    } else if (select.value) {
        input.value = select.value;
        select.style.display = 'block';
        input.style.display = 'none';
    }

    // Пересчитываем слоты
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    days.forEach(day => {
        if (document.getElementById(day + '_active').checked) {
            calculateSlots(day);
        }
    });
}

function toggleTimeInputs(day) {
    const checkbox = document.getElementById(day + '_active');
    const startInput = document.getElementById(day + '_start');
    const endInput = document.getElementById(day + '_end');
    const slotsInfo = document.getElementById(day + '_slots_info');

    if (checkbox.checked) {
        startInput.disabled = false;
        endInput.disabled = false;
        startInput.required = true;
        endInput.required = true;
        calculateSlots(day);
    } else {
        startInput.disabled = true;
        endInput.disabled = true;
        startInput.required = false;
        endInput.required = false;
        slotsInfo.innerHTML = '';
        slotsInfo.className = 'slots-info';
    }
}

function calculateSlots(day) {
    const startInput = document.getElementById(day + '_start');
    const endInput = document.getElementById(day + '_end');
    const intervalSelect = document.getElementById('appointment_interval_select');
    const intervalInput = document.getElementById('appointment_interval');
    const slotsInfo = document.getElementById(day + '_slots_info');
    const isUnlimited = document.getElementById('unlimited_appointments').checked;

    const start = startInput.value;
    const end = endInput.value;

    if (!start || !end) {
        slotsInfo.innerHTML = '<span class="text-muted">Укажите время</span>';
        slotsInfo.className = 'slots-info';
        return;
    }

    if (isUnlimited) {
        slotsInfo.innerHTML = '♾️ <strong>Неограниченно</strong><br><small>Любое количество записей</small>';
        slotsInfo.className = 'slots-info info';
        return;
    }

    const interval = parseInt(intervalInput.value || intervalSelect.value);

    if (!interval) {
        slotsInfo.innerHTML = '<span class="text-muted">Выберите интервал</span>';
        slotsInfo.className = 'slots-info';
        return;
    }

    const startTime = new Date(`2000-01-01 ${start}`);
    const endTime = new Date(`2000-01-01 ${end}`);
    const diffMinutes = (endTime - startTime) / (1000 * 60);

    if (diffMinutes <= 0) {
        slotsInfo.innerHTML = '❌ <strong>Неверное время</strong><br><small>Окончание должно быть позже начала</small>';
        slotsInfo.className = 'slots-info warning';
        return;
    }

    const slotsCount = Math.floor(diffMinutes / interval);
    const totalHours = Math.round(diffMinutes / 60 * 10) / 10;

    if (slotsCount > 0) {
        slotsInfo.innerHTML = `✅ <strong>${slotsCount} слотов</strong><br><small>${totalHours}ч работы по ${interval}мин</small>`;
        slotsInfo.className = 'slots-info success';
    } else {
        slotsInfo.innerHTML = '⚠️ <strong>Мало времени</strong><br><small>Увеличьте рабочие часы или уменьшите интервал</small>';
        slotsInfo.className = 'slots-info warning';
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    const intervalSelect = document.getElementById('appointment_interval_select');
    const intervalInput = document.getElementById('appointment_interval');

    // Инициализация типа записи
    toggleAppointmentType();

    days.forEach(day => {
        toggleTimeInputs(day);

        // Слушатели для пересчета слотов
        document.getElementById(day + '_start').addEventListener('change', () => calculateSlots(day));
        document.getElementById(day + '_end').addEventListener('change', () => calculateSlots(day));
    });

    // Слушатели для интервала
    intervalSelect.addEventListener('change', updateIntervalInput);
    intervalInput.addEventListener('input', function() {
        days.forEach(day => {
            if (document.getElementById(day + '_active').checked) {
                calculateSlots(day);
            }
        });
    });

    // Возвращение к селекту при фокусе на input
    intervalInput.addEventListener('blur', function() {
        if (this.value && this.style.display !== 'none') {
            const select = document.getElementById('appointment_interval_select');
            // Проверяем, есть ли такое значение в селекте
            const option = Array.from(select.options).find(opt => opt.value === this.value);
            if (option) {
                select.value = this.value;
                select.style.display = 'block';
                this.style.display = 'none';
            }
        }
    });

    // Показываем input если значение не из списка
    const currentValue = intervalInput.value;
    const hasOption = Array.from(intervalSelect.options).some(opt => opt.value === currentValue);
    if (currentValue && !hasOption) {
        intervalSelect.value = 'custom';
        updateIntervalInput();
    }

    // Инициализация состояния чекбоксов услуг
    const catalogs = document.querySelectorAll('.catalog-checkbox');
    catalogs.forEach(catalogCheckbox => {
        const catalogId = catalogCheckbox.id.replace('catalog_', '');
        updateCatalogCheckbox(catalogId);
        updateCounts(catalogId);
    });

    // Обработчики для label - открывают/закрывают разделы
    document.querySelectorAll('.catalog-label').forEach(label => {
        label.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const catalogId = this.getAttribute('data-catalog-id');
            toggleCatalogCollapse(catalogId);
        });
    });

    document.querySelectorAll('.subcatalog-label').forEach(label => {
        label.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const subCatalogId = this.getAttribute('data-subcatalog-id');
            toggleSubCatalogCollapse(subCatalogId);
        });
    });
});

// Функции для сворачивания/разворачивания
function toggleCatalogCollapse(catalogId) {
    const content = document.getElementById('catalog_content_' + catalogId);
    const icon = document.getElementById('catalog_icon_' + catalogId);

    content.classList.toggle('show');
    icon.classList.toggle('rotated');
}

function toggleSubCatalogCollapse(subCatalogId) {
    const content = document.getElementById('subcatalog_content_' + subCatalogId);
    const icon = document.getElementById('subcatalog_icon_' + subCatalogId);

    content.classList.toggle('show');
    icon.classList.toggle('rotated');
}

// Функции для управления древовидной структурой услуг
function toggleCatalogServices(catalogId) {
    const catalogCheckbox = document.getElementById('catalog_' + catalogId);
    const subCatalogCheckboxes = document.querySelectorAll(`[data-catalog="${catalogId}"].subcatalog-checkbox`);
    const serviceCheckboxes = document.querySelectorAll(`[data-catalog="${catalogId}"].service-checkbox`);

    subCatalogCheckboxes.forEach(cb => {
        cb.checked = catalogCheckbox.checked;
        cb.indeterminate = false;
    });

    serviceCheckboxes.forEach(cb => {
        cb.checked = catalogCheckbox.checked;
    });

    updateCounts(catalogId);
}

function toggleSubCatalogServices(subCatalogId, catalogId) {
    const subCatalogCheckbox = document.getElementById('subcatalog_' + subCatalogId);
    const serviceCheckboxes = document.querySelectorAll(`[data-subcatalog="${subCatalogId}"].service-checkbox`);

    serviceCheckboxes.forEach(cb => {
        cb.checked = subCatalogCheckbox.checked;
    });

    // Обновляем состояние чекбокса каталога
    updateCatalogCheckbox(catalogId);
    updateCounts(catalogId);
}

function updateSubCatalogCheckbox(subCatalogId, catalogId) {
    const serviceCheckboxes = document.querySelectorAll(`[data-subcatalog="${subCatalogId}"].service-checkbox`);
    const subCatalogCheckbox = document.getElementById('subcatalog_' + subCatalogId);
    const checkedServices = Array.from(serviceCheckboxes).filter(cb => cb.checked);

    if (checkedServices.length === 0) {
        subCatalogCheckbox.checked = false;
        subCatalogCheckbox.indeterminate = false;
    } else if (checkedServices.length === serviceCheckboxes.length) {
        subCatalogCheckbox.checked = true;
        subCatalogCheckbox.indeterminate = false;
    } else {
        subCatalogCheckbox.checked = false;
        subCatalogCheckbox.indeterminate = true;
    }

    // Обновляем состояние чекбокса каталога
    updateCatalogCheckbox(catalogId);
    updateCounts(catalogId);
}

function syncServiceDuplicates(sourceCheckbox) {
    const serviceId = sourceCheckbox.getAttribute('data-service') || sourceCheckbox.value;
    document.querySelectorAll(`.service-checkbox[data-service="${serviceId}"]`).forEach(function (cb) {
        if (cb === sourceCheckbox) return;
        cb.checked = sourceCheckbox.checked;
        const subId = cb.getAttribute('data-subcatalog');
        const catId = cb.getAttribute('data-catalog');
        if (subId && catId) {
            updateSubCatalogCheckbox(subId, catId);
        }
    });
}

function updateCatalogCheckbox(catalogId) {
    const catalogCheckbox = document.getElementById('catalog_' + catalogId);
    const serviceCheckboxes = document.querySelectorAll(`[data-catalog="${catalogId}"].service-checkbox`);
    const checkedServices = Array.from(serviceCheckboxes).filter(cb => cb.checked);

    if (checkedServices.length === 0) {
        catalogCheckbox.checked = false;
        catalogCheckbox.indeterminate = false;
    } else if (checkedServices.length === serviceCheckboxes.length) {
        catalogCheckbox.checked = true;
        catalogCheckbox.indeterminate = false;
    } else {
        catalogCheckbox.checked = false;
        catalogCheckbox.indeterminate = true;
    }
}

function updateCounts(catalogId) {
    // Обновляем счетчик каталога
    const catalogServices = document.querySelectorAll(`[data-catalog="${catalogId}"].service-checkbox`);
    const checkedCatalogServices = Array.from(catalogServices).filter(cb => cb.checked).length;
    const catalogCount = document.getElementById('catalog_count_' + catalogId);
    if (catalogCount) {
        catalogCount.textContent = checkedCatalogServices;
        catalogCount.style.display = checkedCatalogServices > 0 ? 'inline' : 'none';
    }

    // Обновляем счетчики подкаталогов
    const subCatalogs = document.querySelectorAll(`[data-catalog="${catalogId}"].subcatalog-checkbox`);
    subCatalogs.forEach(subCatalogCheckbox => {
        const subCatalogId = subCatalogCheckbox.id.replace('subcatalog_', '');
        const subCatalogServices = document.querySelectorAll(`[data-subcatalog="${subCatalogId}"].service-checkbox`);
        const checkedSubCatalogServices = Array.from(subCatalogServices).filter(cb => cb.checked).length;
        const subCatalogCount = document.getElementById('subcatalog_count_' + subCatalogId);
        if (subCatalogCount) {
            subCatalogCount.textContent = checkedSubCatalogServices;
            subCatalogCount.style.display = checkedSubCatalogServices > 0 ? 'inline' : 'none';
        }
    });
}
// Управление конкретными датами
let scheduleDateIndex = 1;

function addScheduleDate() {
    const container = document.getElementById('schedule-dates-container');
    const newDateItem = document.createElement('div');
    newDateItem.className = 'schedule-date-item mb-3 p-3 border rounded';
    newDateItem.innerHTML = `
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label">Дата</label>
                <input type="date" class="form-control" name="schedule_dates[${scheduleDateIndex}][date]">
            </div>
            <div class="col-md-3">
                <label class="form-label">Начало</label>
                <input type="time" class="form-control" name="schedule_dates[${scheduleDateIndex}][start_time]" value="09:00">
            </div>
            <div class="col-md-3">
                <label class="form-label">Окончание</label>
                <input type="time" class="form-control" name="schedule_dates[${scheduleDateIndex}][end_time]" value="18:00">
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="schedule_dates[${scheduleDateIndex}][is_active]" value="1" checked>
                    <label class="form-check-label">Активна</label>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeScheduleDate(this)">×</button>
            </div>
        </div>
    `;
    container.appendChild(newDateItem);
    scheduleDateIndex++;
}

function removeScheduleDate(button) {
    const dateItem = button.closest('.schedule-date-item');
    dateItem.remove();
}
</script>
@endsection
