@extends('layouts.client')

@section('content')
<div class="content-section">
    <h2 class="page-title">Запись на приём — {{ $service->name }}</h2>

    <div class="card" style="padding: 1rem;">
        <form action="{{ route('booking.store') }}" method="POST" class="row g-3">
            @csrf
            <input type="hidden" name="service_id" value="{{ $service->id }}">

            <div class="col-md-6">
                <label class="form-label">Выберите врача</label>
                <select name="schedule_id" class="form-select" required>
                    <option value="">— Выбрать —</option>
                    @foreach($schedules as $schedule)
                        <option value="{{ $schedule->id }}">{{ $schedule->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Дата</label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Время (если требуется)</label>
                <input type="time" name="time" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Ваше ФИО</label>
                <input type="text" name="client_name" class="form-control" placeholder="Иванов Иван" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Телефон</label>
                <input type="tel" name="client_phone" class="form-control" placeholder="+7 (___) ___-__-__" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">ИИН (необязательно)</label>
                <input type="text" name="patient_iin" class="form-control" maxlength="12">
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
@endsection


