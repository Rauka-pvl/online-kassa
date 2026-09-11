@extends('layouts.client')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-calendar-check"></i> Запись {{ $appointment->code }}
    </li>
@endsection

@section('content')
@php
    $justCreated = session('just_created');
    $isCancelled = $appointment->status === 'cancelled';
    $whatsapp = 'https://api.whatsapp.com/send?phone=77051484470&text=' . urlencode('Здравствуйте, запись ' . $appointment->code);
@endphp
<div class="confirm-page">
    @if(session('success'))
        <div class="alert-success-banner">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error" style="margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-circle"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="confirm-success {{ $isCancelled ? 'is-cancelled' : '' }}">
        <div class="success-icon">
            <i class="fas {{ $isCancelled ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
        </div>
        <h1 class="success-title">
            @if($isCancelled)
                Запись отменена
            @elseif($justCreated)
                Запись оформлена
            @else
                Ваша запись
            @endif
        </h1>
        <p class="success-message">
            @if($isCancelled)
                Слот снова свободен. При необходимости можно записаться заново.
            @else
                Оплата в клинике. Регистратор подтвердит приём.
            @endif
        </p>
    </div>

    <div class="ticket-card">
        <div class="ticket-code">{{ $appointment->code }}</div>
        <div class="ticket-status status-{{ $appointment->status }}">{{ $appointment->status_label }}</div>

        <div class="details-list">
            <div class="detail-item">
                <div class="detail-icon"><i class="fas fa-user"></i></div>
                <div class="detail-content">
                    <span class="detail-label">Пациент</span>
                    <span class="detail-value">{{ $appointment->client_name }}</span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-icon"><i class="fas fa-phone"></i></div>
                <div class="detail-content">
                    <span class="detail-label">Телефон</span>
                    <span class="detail-value">{{ $appointment->client_phone }}</span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-icon"><i class="fas fa-stethoscope"></i></div>
                <div class="detail-content">
                    <span class="detail-label">Услуга</span>
                    <span class="detail-value">{{ $appointment->service->name }}</span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-icon"><i class="fas fa-user-md"></i></div>
                <div class="detail-content">
                    <span class="detail-label">Врач</span>
                    <span class="detail-value">{{ $appointment->schedule->user->name }}</span>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-icon"><i class="fas fa-calendar"></i></div>
                <div class="detail-content">
                    <span class="detail-label">Дата</span>
                    <span class="detail-value">{{ $appointment->formattedDate() }}</span>
                </div>
            </div>
            @if($appointment->formattedTime())
                <div class="detail-item">
                    <div class="detail-icon"><i class="fas fa-clock"></i></div>
                    <div class="detail-content">
                        <span class="detail-label">Время</span>
                        <span class="detail-value">{{ $appointment->formattedTime() }}</span>
                    </div>
                </div>
            @endif
            <div class="detail-item highlight">
                <div class="detail-icon"><i class="fas fa-clinic-medical"></i></div>
                <div class="detail-content">
                    <span class="detail-label">К оплате в клинике</span>
                    <span class="detail-value price">{{ $appointment->service->formatted_price }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="confirm-actions">
        @if($appointment->canBeCancelledByPatient())
            <form method="POST" action="{{ route('booking.cancel', $appointment->manage_token) }}"
                  onsubmit="return confirm('Отменить запись {{ $appointment->code }}?');">
                @csrf
                <button type="submit" class="btn-danger-large">
                    <i class="fas fa-times"></i>
                    <span>Отменить запись</span>
                </button>
            </form>
        @endif
        <a href="{{ $whatsapp }}" class="btn-secondary-large" target="_blank" rel="noopener">
            <i class="fab fa-whatsapp"></i>
            <span>Написать в клинику</span>
        </a>
        <a href="{{ route('main') }}" class="btn-primary-large">
            <i class="fas fa-home"></i>
            <span>На главную</span>
        </a>
    </div>
</div>
@endsection
