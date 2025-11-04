{{-- resources/views/admin/schedules/edit.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать график работы</h1>
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
                <form action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">Врач *</label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                <option value="">Выберите врача</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}"
                                            {{ old('user_id', $schedule->user_id) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }} - {{ $doctor->specialization ?? 'Специализация не указана' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Статус</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    График активен
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Рабочие дни и время</h5>
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
                                                               {{ old($dayKey . '_active', $schedule->{$dayKey . '_active'}) ? 'checked' : '' }}
                                                               onchange="toggleTimeInputs('{{ $dayKey }}')">
                                                        <label class="form-check-label fw-bold" for="{{ $dayKey }}_active">
                                                            {{ $dayName }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="{{ $dayKey }}_start" class="form-label">Начало работы</label>
                                                    <input type="time" class="form-control time-input"
                                                           id="{{ $dayKey }}_start" name="{{ $dayKey }}_start"
                                                           value="{{ old($dayKey . '_start', $schedule->{$dayKey . '_start'}) }}"
                                                           {{ old($dayKey . '_active', $schedule->{$dayKey . '_active'}) ? '' : 'disabled' }}>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="{{ $dayKey }}_end" class="form-label">Конец работы</label>
                                                    <input type="time" class="form-control time-input"
                                                           id="{{ $dayKey }}_end" name="{{ $dayKey }}_end"
                                                           value="{{ old($dayKey . '_end', $schedule->{$dayKey . '_end'}) }}"
                                                           {{ old($dayKey . '_active', $schedule->{$dayKey . '_active'}) ? '' : 'disabled' }}>
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
                                       value="{{ old('start_date', $schedule->start_date) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Дата окончания</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                       id="end_date" name="end_date"
                                       value="{{ old('end_date', $schedule->end_date) }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-text">График будет действовать в указанный период, включая начальную и конечную даты.</div>
                    </div>
                    <div class="mb-4">
                        <h5>Услуги врача *</h5>
                        <div class="row">
                            @foreach($services as $service)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input @error('services') is-invalid @enderror"
                                               type="checkbox" id="service_{{ $service->id }}"
                                               name="services[]" value="{{ $service->id }}"
                                               {{ in_array($service->id, old('services', $schedule->services->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="service_{{ $service->id }}">
                                            {{ $service->name }}
                                            <small class="text-muted d-block">
                                                {{ $service->formatted_price }}
                                                @if($service->duration)
                                                    - {{ $service->duration }} мин
                                                @else
                                                    - без времени
                                                @endif
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
                            Обновить график
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleTimeInputs(day) {
        const checkbox = document.getElementById(day + '_active');
        const startInput = document.getElementById(day + '_start');
        const endInput = document.getElementById(day + '_end');

        if (checkbox.checked) {
            startInput.disabled = false;
            endInput.disabled = false;
            startInput.required = true;
            endInput.required = true;
        } else {
            startInput.disabled = true;
            endInput.disabled = true;
            startInput.required = false;
            endInput.required = false;
        }
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        days.forEach(day => {
            toggleTimeInputs(day);
        });
    });
</script>
@endsection
