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
                        <h5>Услуги врача *</h5>
                        <div class="alert alert-warning" id="services_notice">
                            <strong>⚠️ Важно:</strong> Все выбранные услуги будут доступны в рамках установленного интервала времени.
                        </div>
                        <div class="row">
                            @foreach($services as $service)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input @error('services') is-invalid @enderror"
                                               type="checkbox" id="service_{{ $service->id }}"
                                               name="services[]" value="{{ $service->id }}"
                                               {{ in_array($service->id, old('services', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="service_{{ $service->id }}">
                                            {{ $service->name }}
                                            <small class="text-muted d-block">
                                                {{ $service->formatted_price }}
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('services')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
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
.slots-info {
    padding: 8px 12px;
    background-color: #f8f9fa;
    border-radius: 6px;
    min-height: 40px;
    display: flex;
    align-items: center;
    font-size: 14px;
}

.slots-info.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.slots-info.warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.slots-info.info {
    background-color: #cce7ff;
    color: #0a58ca;
    border: 1px solid #9ec5fe;
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
});
</script>
@endsection
