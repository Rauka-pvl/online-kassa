@extends('layouts.client')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-check-circle"></i> Подтверждение записи
    </li>
@endsection

@section('content')
<div class="confirm-page">
    @if($appointment)
        <div class="confirm-success">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="success-title">Запись успешно создана!</h1>
            <p class="success-message">Ваша запись на приём была успешно оформлена</p>
        </div>

        <div class="confirm-details">
            <div class="details-card">
                <h3 class="details-title">
                    <i class="fas fa-info-circle"></i>
                    Детали записи
                </h3>
                
                <div class="details-list">
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Пациент</span>
                            <span class="detail-value">{{ $appointment->client_name }}</span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Телефон</span>
                            <span class="detail-value">{{ $appointment->client_phone }}</span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Услуга</span>
                            <span class="detail-value">{{ $appointment->service->name }}</span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Врач</span>
                            <span class="detail-value">{{ $appointment->schedule->user->name }}</span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Дата</span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d.m.Y') }}</span>
                        </div>
                    </div>

                    @if($appointment->appointment_time)
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Время</span>
                                <span class="detail-value">{{ $appointment->appointment_time }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="detail-item highlight">
                        <div class="detail-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="detail-content">
                            <span class="detail-label">Стоимость</span>
                            <span class="detail-value price">{{ $appointment->service->formatted_price }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="payment-notice">
                <div class="notice-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="notice-content">
                    <h4>Информация об оплате</h4>
                    <p>Это тестовый шаг оплаты. Оплата не выполняется. Ваша запись создана в статусе «ожидает подтверждения».</p>
                </div>
            </div>

            <div class="confirm-actions">
                <a href="{{ route('main') }}" class="btn-primary-large">
                    <i class="fas fa-home"></i>
                    <span>На главную</span>
                </a>
                <a href="{{ route('catalog') }}" class="btn-secondary-large">
                    <i class="fas fa-list"></i>
                    <span>Посмотреть другие услуги</span>
                </a>
            </div>
        </div>
    @else
        <div class="confirm-error">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="error-title">Детали записи не найдены</h2>
            <p class="error-message">К сожалению, не удалось найти информацию о вашей записи.</p>
            <a href="{{ route('main') }}" class="btn-primary-large">
                <i class="fas fa-home"></i>
                <span>На главную</span>
            </a>
        </div>
    @endif
</div>
@endsection


