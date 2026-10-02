@extends('layouts.client')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('catalog') }}">Каталог</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-user-md"></i> {{ $doctor->name }}
    </li>
@endsection

@section('content')
<div class="services-page">
    <div class="page-header">
        <h1 class="page-title-main">{{ $doctor->name }}</h1>
        <p class="page-subtitle">
            {{ $doctor->specialization ?: 'Врач' }} — выберите услугу, чтобы записаться
        </p>
    </div>

    <div class="services-container">
        @forelse($services as $service)
            <div class="service-card-modern">
                <div class="service-card-header">
                    <div class="service-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="service-header-content">
                        <h3 class="service-title">{{ $service->name }}</h3>
                        @if($service->description)
                            <p class="service-description">{{ $service->description }}</p>
                        @endif
                    </div>
                </div>

                <div class="service-card-footer">
                    <div class="service-price-wrapper">
                        <span class="service-price-label">К оплате в клинике:</span>
                        <span class="service-price-value">{{ $service->formatted_price }}</span>
                    </div>
                    <a href="{{ route('service.booking', array_filter([
                            'service' => $service,
                            'schedule' => $service->booking_schedule_id,
                            'sub_catalog' => $service->booking_sub_catalog_id ?? null,
                        ])) }}"
                       class="service-book-btn">
                        <span>Записаться</span>
                        <i class="fas fa-calendar-check"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>У этого врача пока нет доступных услуг для онлайн-записи</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
