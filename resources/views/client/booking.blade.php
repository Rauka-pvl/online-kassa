@extends('layouts.client')

@section('content')
<div class="content-section">
    <h2 class="page-title">Запись на приём — {{ $service->name }}</h2>

    <div class="card" style="padding: 1rem;">
        <form action="{{ route('booking.store') }}" method="POST" class="row g-3" id="bookingForm">
            @csrf
            <input type="hidden" name="service_id" value="{{ $service->id }}">

            <div class="col-md-6">
                <label class="form-label">Выберите врача</label>
                <select name="schedule_id" id="schedule_select" class="form-select" required
                        data-slots-url-base="{{ url('/api/schedules') }}">
                    <option value="">— Выбрать —</option>
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

            <div class="col-md-3">
                <label class="form-label">Дата</label>
                <input type="date" name="date" id="date_input" class="form-control"
                       value="{{ old('date', date('Y-m-d')) }}" required>
            </div>

            <div class="col-md-3" id="time_column">
                <label class="form-label">Время приёма</label>
                <div class="position-relative">
                    <select class="form-select d-none" id="slot_select" data-previous="{{ old('time') }}"></select>
                    <input type="time" class="form-control d-none" id="manual_time_input"
                           value="{{ old('time') }}">
                    <div class="position-absolute top-50 end-0 translate-middle-y me-3 d-none" id="slot_loader">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                    </div>
                </div>
                <div class="form-text" id="slot_message"></div>
                @error('time')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Ваше ФИО</label>
                <input type="text" name="client_name" class="form-control" placeholder="Иванов Иван"
                       value="{{ old('client_name') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Телефон</label>
                <input type="tel" name="client_phone" id="phone_input" class="form-control"
                       placeholder="+7 (___) ___-__-__" value="{{ old('client_phone') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">ИИН (необязательно)</label>
                <input type="text" name="patient_iin" class="form-control" maxlength="12"
                       value="{{ old('patient_iin') }}">
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Перейти к оплате</button>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Назад</a>
            </div>

            @if ($errors->any())
                <div class="col-12">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </form>
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

                slotMessage.textContent = 'Выберите врача и дату, чтобы увидеть доступное время.';
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
                    slotMessage.textContent = `Рабочее время врача: ${workingHours.start} — ${workingHours.end}`;
                } else {
                    slotMessage.textContent = '';
                }
            }

            function enableSlotMode(slots, workingHours) {
                resetInputs();

                if (!slots || slots.length === 0) {
                    slotMessage.textContent = 'На выбранный день свободных окон нет. Попробуйте выбрать другую дату.';
                    return;
                }

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
                    slotMessage.textContent = `Рабочее время врача: ${workingHours.start} — ${workingHours.end}`;
                } else {
                    slotMessage.textContent = '';
                }
            }

            async function loadSlots() {
                const scheduleId = scheduleSelect.value;
                const dateValue = dateInput.value;

                if (!scheduleId || !dateValue) {
                    resetInputs();
                    return;
                }

                slotLoader.classList.remove('d-none');
                slotMessage.textContent = '';

                try {
                    const response = await fetch(`${baseUrl}/${scheduleId}/slots?date=${dateValue}`);
                    if (!response.ok) {
                        throw new Error('Ошибка ответа');
                    }

                    const data = await response.json();

                    if (!data.working) {
                        resetInputs();
                        slotMessage.textContent = 'В выбранный день врач не принимает пациентов.';
                        return;
                    }

                    const isUnlimited = scheduleSelect.options[scheduleSelect.selectedIndex].dataset.unlimited === '1' || data.unlimited;

                    if (isUnlimited) {
                        enableManualMode(data.working_hours);
                    } else {
                        enableSlotMode(data.slots || [], data.working_hours);
                    }
                } catch (error) {
                    resetInputs();
                    slotMessage.textContent = 'Не удалось загрузить доступное время. Повторите попытку позже.';
                } finally {
                    slotLoader.classList.add('d-none');
                }
            }

            scheduleSelect.addEventListener('change', loadSlots);
            dateInput.addEventListener('change', loadSlots);

            if (scheduleSelect.value) {
                loadSlots();
            } else {
                resetInputs();
            }
        });
    </script>
@endpush
@endsection


