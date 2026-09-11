@extends('layouts.client')

@php
    $subCatalog = $service->subCatalog;
    $catalog = $subCatalog->catalog ?? null;
    $startStep = old('client_name') || $errors->any() ? 3 : 1;
    $selectedSchedule = old('schedule_id', request('schedule', $schedules->count() === 1 ? optional($schedules->first())->id : ''));
@endphp

@section('breadcrumb')
    @if($catalog)
        <li class="breadcrumb-item">
            <a href="{{ route('catalog') }}">{{ $catalog->name }}</a>
        </li>
    @endif
    @if($subCatalog)
        <li class="breadcrumb-item">
            <a href="{{ route('services', ['id' => $subCatalog->id]) }}">{{ $subCatalog->name }}</a>
        </li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-calendar-check"></i> Запись на приём
    </li>
@endsection

@section('content')
<div class="booking-page">
    <div class="page-header">
        <h1 class="page-title-main">Запись на приём</h1>
        <p class="page-subtitle">Три простых шага — без онлайн-оплаты</p>
    </div>

    <div class="booking-container">
        <div class="booking-service-info">
            <div class="service-info-card">
                <div class="service-info-icon">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="service-info-content">
                    <h3 class="service-info-title">{{ $service->name }}</h3>
                    <p class="service-info-price">{{ $service->formatted_price }}</p>
                    <p class="service-info-pay-hint">К оплате в клинике</p>
                </div>
            </div>
        </div>

        <ol class="booking-steps" aria-label="Шаги записи">
            <li class="booking-step-tab {{ $startStep === 1 ? 'is-active' : '' }}" data-step-tab="1">
                <span class="booking-step-num">1</span>
                <span>Врач</span>
            </li>
            <li class="booking-step-tab {{ $startStep === 2 ? 'is-active' : '' }}" data-step-tab="2">
                <span class="booking-step-num">2</span>
                <span>Дата и время</span>
            </li>
            <li class="booking-step-tab {{ $startStep === 3 ? 'is-active' : '' }}" data-step-tab="3">
                <span class="booking-step-num">3</span>
                <span>Ваши данные</span>
            </li>
        </ol>

        <div class="booking-form-wrapper">
            <form action="{{ route('booking.store') }}" method="POST" id="bookingForm" class="booking-form" data-start-step="{{ $startStep }}" novalidate>
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">
                <input type="hidden" name="time" id="time_hidden" value="{{ old('time') }}">

                <div class="booking-step {{ $startStep === 1 ? 'is-active' : '' }}" data-step="1">
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-user-md"></i>
                            Выберите врача
                        </h3>

                        <div class="form-group">
                            <label class="form-label" for="schedule_select">Врач</label>
                            <select name="schedule_id" id="schedule_select" class="form-control-modern" required
                                    data-slots-url-base="{{ url('/api/schedules') }}">
                                <option value="">— Выбрать врача —</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}"
                                            data-unlimited="{{ $schedule->hasUnlimitedAppointments() ? '1' : '0' }}"
                                            data-interval="{{ $schedule->appointment_interval }}"
                                            {{ (string) $selectedSchedule === (string) $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('schedule_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="booking-step {{ $startStep === 2 ? 'is-active' : '' }}" data-step="2">
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-calendar"></i>
                            Дата и время
                        </h3>

                        <div class="form-group">
                            <label class="form-label">Дата приёма</label>
                            <input type="hidden" name="date" id="date_input" value="{{ old('date') }}">
                            <div id="booking_calendar" class="booking-calendar" data-days-url-base="{{ url('/api/schedules') }}"></div>
                            <div class="calendar-legend">
                                <span><i class="cal-dot is-open"></i> Есть свободное время</span>
                                <span><i class="cal-dot is-full"></i> Все слоты заняты</span>
                                <span><i class="cal-dot is-closed"></i> Нет приёма</span>
                            </div>
                            @error('date')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group" id="time_column">
                            <label class="form-label">Время приёма</label>
                            <div class="time-input-wrapper">
                                <div class="slot-grid" id="slot_grid"></div>
                                <input type="time" class="form-control-modern d-none" id="manual_time_input"
                                       value="{{ old('time') }}">
                                <div class="time-loader d-none" id="slot_loader">
                                    <div class="spinner"></div>
                                </div>
                            </div>
                            <div class="form-message" id="slot_message">Выберите врача и дату, чтобы увидеть доступное время.</div>
                            @error('time')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="booking-step {{ $startStep === 3 ? 'is-active' : '' }}" data-step="3">
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-user"></i>
                            Ваши данные
                        </h3>

                        <div class="form-group">
                            <label class="form-label" for="client_name">ФИО</label>
                            <input type="text" name="client_name" id="client_name" class="form-control-modern"
                                   placeholder="Иванов Иван Иванович"
                                   value="{{ old('client_name') }}" required>
                            @error('client_name')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="phone_input">Телефон</label>
                                <input type="tel" name="client_phone" id="phone_input" class="form-control-modern"
                                       placeholder="+7 (___) ___-__-__" value="{{ old('client_phone') }}"
                                       inputmode="tel" autocomplete="tel" required>
                                <div class="form-hint">Введите номер полностью: +7 (XXX) XXX-XX-XX</div>
                                @error('client_phone')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="patient_iin">
                                    ИИН <span class="form-label-optional">(необязательно)</span>
                                </label>
                                <input type="text" name="patient_iin" id="patient_iin" class="form-control-modern" maxlength="12"
                                       placeholder="000000000000" inputmode="numeric" value="{{ old('patient_iin') }}">
                                <div class="form-hint">Если указываете ИИН — все 12 цифр</div>
                            </div>
                        </div>

                        <p class="booking-clinic-note">
                            Запись создаётся сразу. Оплата — в клинике при визите. Регистратор подтвердит приём.
                        </p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="form-errors">
                        <div class="alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Проверьте форму:</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="booking-nav">
                    <a href="{{ url()->previous() }}" class="btn-secondary-large" id="bookingBackLink">
                        <i class="fas fa-arrow-left"></i>
                        <span>Назад</span>
                    </a>
                    <button type="button" class="btn-secondary-large d-none" id="prevStepBtn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Назад</span>
                    </button>
                    <button type="button" class="btn-primary-large" id="nextStepBtn">
                        <span>Далее</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn-primary-large d-none" id="submitBookingBtn">
                        <span>Записаться</span>
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('bookingForm');
            const scheduleSelect = document.getElementById('schedule_select');
            const dateInput = document.getElementById('date_input');
            const timeHidden = document.getElementById('time_hidden');
            const slotGrid = document.getElementById('slot_grid');
            const manualTimeInput = document.getElementById('manual_time_input');
            const slotMessage = document.getElementById('slot_message');
            const slotLoader = document.getElementById('slot_loader');
            const phoneInput = document.getElementById('phone_input');
            const nextBtn = document.getElementById('nextStepBtn');
            const prevBtn = document.getElementById('prevStepBtn');
            const submitBtn = document.getElementById('submitBookingBtn');
            const backLink = document.getElementById('bookingBackLink');
            const baseUrl = scheduleSelect.dataset.slotsUrlBase;
            const calendarEl = document.getElementById('booking_calendar');
            const daysUrlBase = calendarEl ? calendarEl.dataset.daysUrlBase : baseUrl;
            let currentStep = parseInt(form.dataset.startStep || '1', 10);
            let previousTime = timeHidden.value || '';
            let calendarCursor = new Date();
            calendarCursor.setDate(1);
            calendarCursor.setHours(0, 0, 0, 0);
            let daysByDate = {};

            const iinInput = document.getElementById('patient_iin');
            const phoneMask = applyKzPhoneMask(phoneInput);
            const iinMask = applyIinMask(iinInput);

            function showInfoMessage(message) {
                slotMessage.textContent = message;
                slotMessage.className = 'form-message';
            }

            function showErrorMessage(message) {
                slotGrid.innerHTML = '';
                manualTimeInput.classList.add('d-none');
                manualTimeInput.required = false;
                timeHidden.value = '';
                slotMessage.textContent = message;
                slotMessage.className = 'form-message form-error';
            }

            function setStep(step) {
                currentStep = step;
                form.querySelectorAll('.booking-step').forEach(function (el) {
                    el.classList.toggle('is-active', parseInt(el.dataset.step, 10) === step);
                });
                document.querySelectorAll('[data-step-tab]').forEach(function (el) {
                    const tabStep = parseInt(el.dataset.stepTab, 10);
                    el.classList.toggle('is-active', tabStep === step);
                    el.classList.toggle('is-done', tabStep < step);
                });

                backLink.classList.toggle('d-none', step > 1);
                prevBtn.classList.toggle('d-none', step === 1);
                nextBtn.classList.toggle('d-none', step === 3);
                submitBtn.classList.toggle('d-none', step !== 3);
            }

            function canGoNext() {
                if (currentStep === 1) {
                    if (!scheduleSelect.value) {
                        showStepAlert('Выберите врача, чтобы продолжить.');
                        return false;
                    }
                    return true;
                }
                if (currentStep === 2) {
                    if (!dateInput.value) {
                        showStepAlert('Выберите дату приёма.');
                        return false;
                    }
                    const timeValue = timeHidden.value || (manualTimeInput.classList.contains('d-none') ? '' : manualTimeInput.value);
                    if (!timeValue) {
                        showStepAlert('Выберите время приёма.');
                        return false;
                    }
                    timeHidden.value = timeValue;
                    return true;
                }
                return true;
            }

            function showStepAlert(message) {
                slotMessage.textContent = message;
                slotMessage.className = 'form-message form-error';
                if (currentStep !== 2) {
                    const existing = form.querySelector('.step-inline-error');
                    if (existing) existing.remove();
                    const note = document.createElement('div');
                    note.className = 'form-error step-inline-error';
                    note.textContent = message;
                    form.querySelector('.booking-step.is-active .form-section').appendChild(note);
                }
            }

            function enableManualMode(workingHours) {
                slotGrid.innerHTML = '';
                manualTimeInput.classList.remove('d-none');
                if (previousTime) {
                    manualTimeInput.value = previousTime;
                    timeHidden.value = previousTime;
                    previousTime = '';
                }
                if (workingHours) {
                    showInfoMessage('Рабочее время врача: ' + formatHours(workingHours) + '. Укажите удобное время.');
                } else {
                    showInfoMessage('Укажите удобное время приёма.');
                }
            }

            function monthKey(date) {
                return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
            }

            function formatMonthTitle(date) {
                return date.toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' });
            }

            function selectDate(value, reloadSlots) {
                dateInput.value = value;
                if (calendarEl) {
                    calendarEl.querySelectorAll('.cal-day').forEach(function (btn) {
                        btn.classList.toggle('is-selected', btn.dataset.date === value);
                    });
                }
                if (reloadSlots !== false) {
                    timeHidden.value = '';
                    loadSlots();
                }
            }

            function renderCalendar() {
                if (!calendarEl) return;

                if (!scheduleSelect.value) {
                    calendarEl.innerHTML = '<div class="cal-placeholder">Сначала выберите врача — в календаре появятся дни приёма.</div>';
                    return;
                }

                const year = calendarCursor.getFullYear();
                const month = calendarCursor.getMonth();
                const first = new Date(year, month, 1);
                const startWeekday = (first.getDay() + 6) % 7;
                const lastDate = new Date(year, month + 1, 0).getDate();
                const todayNow = new Date();
                const todayKey = todayNow.getFullYear() + '-' + String(todayNow.getMonth() + 1).padStart(2, '0') + '-' + String(todayNow.getDate()).padStart(2, '0');
                const now = new Date();
                const canPrev = year > now.getFullYear() || (year === now.getFullYear() && month > now.getMonth());

                let html = '<div class="cal-head">' +
                    '<button type="button" class="cal-nav" data-cal="prev"' + (canPrev ? '' : ' disabled') + ' aria-label="Предыдущий месяц">‹</button>' +
                    '<div class="cal-title">' + formatMonthTitle(calendarCursor) + '</div>' +
                    '<button type="button" class="cal-nav" data-cal="next" aria-label="Следующий месяц">›</button>' +
                    '</div>';
                html += '<div class="cal-weekdays"><span>Пн</span><span>Вт</span><span>Ср</span><span>Чт</span><span>Пт</span><span>Сб</span><span>Вс</span></div>';
                html += '<div class="cal-grid">';

                for (let i = 0; i < startWeekday; i++) {
                    html += '<span></span>';
                }

                for (let day = 1; day <= lastDate; day++) {
                    const value = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                    const info = daysByDate[value] || { status: 'closed' };
                    const classes = ['cal-day', 'is-' + info.status];
                    if (value === dateInput.value) classes.push('is-selected');
                    if (value === todayKey) classes.push('is-today');
                    const title = info.status === 'open'
                        ? (info.free != null ? 'Свободно: ' + info.free : 'Можно записаться')
                        : (info.status === 'full' ? 'Все слоты заняты' : (info.status === 'past' ? 'Прошедшая дата' : 'Врач не принимает'));
                    const disabled = info.status !== 'open' ? ' disabled' : '';
                    const mark = info.status === 'open' ? '<span class="cal-mark"></span>' : '';
                    html += '<button type="button" class="' + classes.join(' ') + '" data-date="' + value + '" title="' + title + '"' + disabled + '>' + day + mark + '</button>';
                }

                html += '</div>';
                calendarEl.innerHTML = html;
            }

            async function loadCalendar() {
                if (!calendarEl) return;
                if (!scheduleSelect.value) {
                    daysByDate = {};
                    renderCalendar();
                    return;
                }

                calendarEl.innerHTML = '<div class="cal-placeholder">Загружаем дни приёма…</div>';
                try {
                    const response = await fetch(daysUrlBase + '/' + scheduleSelect.value + '/days?month=' + monthKey(calendarCursor));
                    const data = await response.json();
                    daysByDate = {};
                    (data.days || []).forEach(function (day) {
                        daysByDate[day.date] = day;
                    });
                    renderCalendar();
                } catch (e) {
                    calendarEl.innerHTML = '<div class="cal-placeholder">Не удалось загрузить календарь. Попробуйте ещё раз.</div>';
                }
            }

            if (calendarEl) {
                calendarEl.addEventListener('click', function (e) {
                    const nav = e.target.closest('[data-cal]');
                    if (nav) {
                        if (nav.disabled) return;
                        calendarCursor.setMonth(calendarCursor.getMonth() + (nav.dataset.cal === 'next' ? 1 : -1));
                        loadCalendar();
                        return;
                    }
                    const dayBtn = e.target.closest('.cal-day.is-open');
                    if (dayBtn) {
                        selectDate(dayBtn.dataset.date, true);
                    }
                });
            }

            function formatHours(workingHours) {
                const start = (workingHours.start || '').toString().slice(0, 5);
                const end = (workingHours.end || '').toString().slice(0, 5);
                return start + ' — ' + end;
            }

            function enableSlotMode(slots, workingHours) {
                manualTimeInput.classList.add('d-none');
                manualTimeInput.value = '';
                slotGrid.innerHTML = '';

                if (!slots || slots.length === 0) {
                    showErrorMessage('На выбранный день свободных окон нет. Попробуйте другую дату.');
                    return;
                }

                slots.forEach(function (slot) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'slot-chip';
                    btn.textContent = slot;
                    btn.dataset.time = slot;
                    if (previousTime === slot || timeHidden.value === slot) {
                        btn.classList.add('is-selected');
                        timeHidden.value = slot;
                    }
                    btn.addEventListener('click', function () {
                        slotGrid.querySelectorAll('.slot-chip').forEach(function (chip) {
                            chip.classList.remove('is-selected');
                        });
                        btn.classList.add('is-selected');
                        timeHidden.value = slot;
                    });
                    slotGrid.appendChild(btn);
                });

                previousTime = '';

                if (workingHours) {
                    showInfoMessage('Рабочее время: ' + formatHours(workingHours) + '. Нажмите на свободное время.');
                } else {
                    showInfoMessage('Нажмите на свободное время.');
                }
            }

            async function loadSlots() {
                const scheduleId = scheduleSelect.value;
                const dateValue = dateInput.value;

                if (!scheduleId || !dateValue) {
                    slotGrid.innerHTML = '';
                    manualTimeInput.classList.add('d-none');
                    showInfoMessage('Выберите врача и дату, чтобы увидеть доступное время.');
                    return;
                }

                slotLoader.classList.remove('d-none');
                slotMessage.textContent = '';
                slotMessage.className = 'form-message';

                try {
                    const response = await fetch(baseUrl + '/' + scheduleId + '/slots?date=' + dateValue);
                    if (!response.ok) {
                        throw new Error('Ошибка ответа');
                    }

                    const data = await response.json();

                    if (data.working !== true) {
                        showErrorMessage('В выбранный день врач не принимает. Выберите другую дату.');
                        return;
                    }

                    const isUnlimited = scheduleSelect.options[scheduleSelect.selectedIndex].dataset.unlimited === '1' || data.unlimited;

                    if (isUnlimited) {
                        enableManualMode(data.working_hours);
                    } else {
                        enableSlotMode(data.slots || [], data.working_hours);
                    }
                } catch (error) {
                    showErrorMessage('Не удалось загрузить доступное время. Повторите попытку позже.');
                } finally {
                    slotLoader.classList.add('d-none');
                }
            }

            scheduleSelect.addEventListener('change', function () {
                dateInput.value = '';
                timeHidden.value = '';
                slotGrid.innerHTML = '';
                manualTimeInput.classList.add('d-none');
                showInfoMessage('Выберите дату, чтобы увидеть доступное время.');
                loadCalendar();
            });

            manualTimeInput.addEventListener('change', function () {
                timeHidden.value = manualTimeInput.value;
            });

            nextBtn.addEventListener('click', function () {
                form.querySelectorAll('.step-inline-error').forEach(function (el) { el.remove(); });
                if (!canGoNext()) return;
                if (currentStep === 1) {
                    setStep(2);
                    loadCalendar();
                    if (dateInput.value) loadSlots();
                    return;
                }
                setStep(3);
            });

            prevBtn.addEventListener('click', function () {
                setStep(Math.max(1, currentStep - 1));
            });

            form.addEventListener('submit', function (e) {
                const nameInput = document.getElementById('client_name');
                if (!scheduleSelect.value || !dateInput.value) {
                    e.preventDefault();
                    setStep(!scheduleSelect.value ? 1 : 2);
                    showStepAlert('Заполните врача, дату и время.');
                    return false;
                }
                const timeValue = timeHidden.value || manualTimeInput.value;
                if (!timeValue) {
                    e.preventDefault();
                    setStep(2);
                    showErrorMessage('Выберите время приёма.');
                    return false;
                }
                if (!nameInput.value.trim() || !isMaskedPhoneComplete(phoneMask, phoneInput)) {
                    e.preventDefault();
                    setStep(3);
                    showStepAlert('Укажите ФИО и полный номер телефона: +7 (XXX) XXX-XX-XX');
                    phoneInput.focus();
                    return false;
                }
                if (iinInput && iinInput.value.trim() !== '' && String(iinMask ? iinMask.unmaskedValue : iinInput.value).length !== 12) {
                    e.preventDefault();
                    setStep(3);
                    showStepAlert('ИИН должен содержать 12 цифр, либо оставьте поле пустым.');
                    iinInput.focus();
                    return false;
                }
                timeHidden.value = timeValue;
            });

            setStep(currentStep);
            loadCalendar();
            if (scheduleSelect.value && dateInput.value) {
                loadSlots();
            }
        });
    </script>
@endpush
