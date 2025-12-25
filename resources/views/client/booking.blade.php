@extends('layouts.client')

@php
    $subCatalog = $service->subCatalog;
    $catalog = $subCatalog->catalog ?? null;
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
        <p class="page-subtitle">Заполните форму для записи на услугу</p>
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
                </div>
            </div>
        </div>

        <div class="booking-form-wrapper">
            <form action="{{ route('booking.store') }}" method="POST" id="bookingForm" class="booking-form">
                @csrf
                <input type="hidden" name="service_id" value="{{ $service->id }}">

                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-user-md"></i>
                        Выбор врача и времени
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-md"></i>
                                Выберите врача
                            </label>
                            <select name="schedule_id" id="schedule_select" class="form-control-modern" required
                                    data-slots-url-base="{{ url('/api/schedules') }}">
                                <option value="">— Выбрать врача —</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}"
                                            data-unlimited="{{ $schedule->hasUnlimitedAppointments() ? '1' : '0' }}"
                                            data-interval="{{ $schedule->appointment_interval }}"
                                            {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i>
                                Дата приёма
                            </label>
                            <input type="date" name="date" id="date_input" class="form-control-modern"
                                   value="{{ old('date', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                        </div>

                        <div class="form-group" id="time_column">
                            <label class="form-label">
                                <i class="fas fa-clock"></i>
                                Время приёма
                            </label>
                            <div class="time-input-wrapper">
                                <select class="form-control-modern d-none" id="slot_select" data-previous="{{ old('time') }}"></select>
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

                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-user"></i>
                        Ваши данные
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-id-card"></i>
                                ФИО
                            </label>
                            <input type="text" name="client_name" class="form-control-modern" 
                                   placeholder="Иванов Иван Иванович"
                                   value="{{ old('client_name') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone"></i>
                                Телефон
                            </label>
                            <input type="tel" name="client_phone" id="phone_input" class="form-control-modern"
                                   placeholder="+7 (___) ___-__-__" value="{{ old('client_phone') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-id-badge"></i>
                                ИИН <span class="form-label-optional">(необязательно)</span>
                            </label>
                            <input type="text" name="patient_iin" class="form-control-modern" maxlength="12"
                                   placeholder="000000000000" value="{{ old('patient_iin') }}">
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="form-errors">
                        <div class="alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Ошибки при заполнении формы:</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="form-actions">
                    <button type="submit" class="btn-primary-large">
                        <span>Перейти к оплате</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <a href="{{ url()->previous() }}" class="btn-secondary-large">
                        <i class="fas fa-arrow-left"></i>
                        <span>Назад</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scheduleSelect = document.getElementById('schedule_select');
            const dateInput = document.getElementById('date_input');
            const slotSelect = document.getElementById('slot_select');
            const manualTimeInput = document.getElementById('manual_time_input');
            const slotMessage = document.getElementById('slot_message');
            const slotLoader = document.getElementById('slot_loader');
            const phoneInput = document.getElementById('phone_input');
            const baseUrl = scheduleSelect.dataset.slotsUrlBase;
            let previousTime = slotSelect.dataset.previous;

            if (window.IMask && phoneInput) {
                IMask(phoneInput, {
                    mask: '+{7}(000)000-00-00'
                });
            }

            function resetInputs() {
                manualTimeInput.classList.add('d-none');
                manualTimeInput.required = false;
                manualTimeInput.name = '';
                manualTimeInput.value = '';

                slotSelect.classList.add('d-none');
                slotSelect.required = false;
                slotSelect.name = '';
                slotSelect.innerHTML = '';
            }

            function showErrorMessage(message) {
                // Скрываем все поля времени
                manualTimeInput.classList.add('d-none');
                manualTimeInput.required = false;
                manualTimeInput.name = '';
                manualTimeInput.value = '';
                manualTimeInput.removeAttribute('required');

                slotSelect.classList.add('d-none');
                slotSelect.required = false;
                slotSelect.name = '';
                slotSelect.innerHTML = '';
                slotSelect.removeAttribute('required');

                // Показываем сообщение об ошибке
                slotMessage.textContent = message;
                slotMessage.className = 'form-message form-error';
            }

            function showInfoMessage(message) {
                slotMessage.textContent = message;
                slotMessage.className = 'form-message';
            }

            function enableManualMode(workingHours) {
                resetInputs();
                manualTimeInput.classList.remove('d-none');
                manualTimeInput.required = true;
                manualTimeInput.name = 'time';

                if (previousTime) {
                    manualTimeInput.value = previousTime;
                    previousTime = '';
                }

                if (workingHours) {
                    showInfoMessage(`Рабочее время врача: ${workingHours.start} — ${workingHours.end}`);
                } else {
                    showInfoMessage('');
                }
            }

            function enableSlotMode(slots, workingHours) {
                // Сначала сбрасываем все поля
                resetInputs();

                // Проверяем, есть ли слоты
                if (!slots || slots.length === 0) {
                    showErrorMessage('На выбранный день свободных окон нет. Попробуйте выбрать другую дату.');
                    return;
                }

                // Только если есть слоты, показываем поле выбора
                slotSelect.classList.remove('d-none');
                slotSelect.required = true;
                slotSelect.name = 'time';

                const options = ['<option value="">— Выбрать время —</option>'];
                slots.forEach(slot => {
                    const selected = previousTime && previousTime === slot ? 'selected' : '';
                    options.push(`<option value="${slot}" ${selected}>${slot}</option>`);
                });
                slotSelect.innerHTML = options.join('');

                if (previousTime) {
                    previousTime = '';
                }

                if (workingHours) {
                    showInfoMessage(`Рабочее время врача: ${workingHours.start} — ${workingHours.end}`);
                } else {
                    showInfoMessage('');
                }
            }

            async function loadSlots() {
                const scheduleId = scheduleSelect.value;
                const dateValue = dateInput.value;

                if (!scheduleId || !dateValue) {
                    resetInputs();
                    showInfoMessage('Выберите врача и дату, чтобы увидеть доступное время.');
                    return;
                }

                slotLoader.classList.remove('d-none');
                slotMessage.textContent = '';
                slotMessage.className = 'form-message';

                try {
                    const response = await fetch(`${baseUrl}/${scheduleId}/slots?date=${dateValue}`);
                    if (!response.ok) {
                        throw new Error('Ошибка ответа');
                    }

                    const data = await response.json();

                    // Строгая проверка: если врач не работает, сразу выходим
                    if (data.working === false) {
                        showErrorMessage('В выбранный день врач не принимает пациентов. Выберите другую дату.');
                        return; // Важно: return здесь предотвращает дальнейшее выполнение
                    }

                    // Дополнительная проверка: если working не true, считаем что врач не работает
                    if (data.working !== true) {
                        showErrorMessage('В выбранный день врач не принимает пациентов. Выберите другую дату.');
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

            scheduleSelect.addEventListener('change', function() {
                // При смене врача сбрасываем дату и время
                dateInput.value = '';
                resetInputs();
                showInfoMessage('Выберите дату, чтобы увидеть доступное время.');
            });
            
            dateInput.addEventListener('change', loadSlots);

            // При загрузке страницы, если уже выбраны врач и дата, загружаем слоты
            if (scheduleSelect.value && dateInput.value) {
                loadSlots();
            } else {
                resetInputs();
                showInfoMessage('Выберите врача и дату, чтобы увидеть доступное время.');
            }

            // Предотвращаем отправку формы, если время не выбрано
            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                const timeInput = slotSelect.name === 'time' ? slotSelect : (manualTimeInput.name === 'time' ? manualTimeInput : null);
                
                if (!timeInput || !timeInput.value) {
                    // Проверяем, не скрыто ли поле из-за того, что врач не работает
                    if (slotSelect.classList.contains('d-none') && manualTimeInput.classList.contains('d-none')) {
                        const message = slotMessage.textContent;
                        if (message.includes('не принимает пациентов') || message.includes('свободных окон нет')) {
                            e.preventDefault();
                            alert('Нельзя записаться на день, когда врач не принимает пациентов. Выберите другую дату.');
                            return false;
                        }
                    }
                }
            });
        });
    </script>
@endpush
@endsection


