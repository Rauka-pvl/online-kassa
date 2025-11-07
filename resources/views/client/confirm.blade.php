@extends('layouts.client')

@section('content')
<div class="content-section">
    <h2 class="page-title">Подтверждение записи</h2>

    @if($appointment)
        <div class="card" style="padding:1rem;">
            <p class="mb-2"><strong>Пациент:</strong> {{ $appointment->client_name }}</p>
            <p class="mb-2"><strong>Телефон:</strong> {{ $appointment->client_phone }}</p>
            <p class="mb-2"><strong>Услуга:</strong> {{ $appointment->service->name }}</p>
            <p class="mb-2"><strong>Врач:</strong> {{ $appointment->schedule->user->name }}</p>
            <p class="mb-2"><strong>Дата:</strong> {{ $appointment->appointment_date }}</p>
            @if($appointment->appointment_time)
                <p class="mb-2"><strong>Время:</strong> {{ $appointment->appointment_time }}</p>
            @endif
            <p class="mb-2"><strong>Стоимость:</strong> {{ $appointment->service->formatted_price }}</p>

            <div class="alert alert-info mt-3">
                Это тестовый шаг оплаты. Оплата не выполняется. Ваша запись создана в статусе «ожидает».
            </div>

            <a href="{{ route('main') }}" class="btn btn-primary mt-2">На главную</a>
        </div>
    @else
        <div class="alert alert-warning">Детали записи не найдены.</div>
    @endif
</div>
@endsection


