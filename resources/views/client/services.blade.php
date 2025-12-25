@extends('layouts.client')

@php
    $catalog = $subCatalog->catalog ?? null;
@endphp

@section('breadcrumb')
    @if($catalog)
        <li class="breadcrumb-item">
            <a href="{{ route('catalog') }}">{{ $catalog->name }}</a>
        </li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-list"></i> {{ $subCatalog->name }}
    </li>
@endsection

@section('content')
<div class="services-page">
    <div class="page-header">
        <h1 class="page-title-main">{{ $subCatalog->name }}</h1>
        <p class="page-subtitle">Выберите интересующую вас услугу</p>
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
                
                <div class="service-card-body">
                    @if($service->schedules->count() > 0)
                        <div class="service-doctors">
                            <div class="service-doctors-label">
                                <i class="fas fa-user-md"></i>
                                <span>Доступные врачи ({{ $service->schedules->count() }}):</span>
                            </div>
                            <div class="service-doctors-list">
                                @foreach($service->schedules as $schedule)
                                    <span class="doctor-badge">
                                        <i class="fas fa-user"></i>
                                        {{ $schedule->user->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="service-no-doctors">
                            <i class="fas fa-info-circle"></i>
                            <span>Врачи для этой услуги пока не назначены</span>
                        </div>
                    @endif
                </div>

                <div class="service-card-footer">
                    <div class="service-price-wrapper">
                        <span class="service-price-label">Стоимость:</span>
                        <span class="service-price-value">{{ $service->formatted_price }}</span>
                    </div>
                    @if($service->schedules->count() > 0)
                        <a href="{{ route('service.booking', $service) }}" class="service-book-btn">
                            <span>Записаться</span>
                            <i class="fas fa-calendar-check"></i>
                        </a>
                    @else
                        <button class="service-book-btn" disabled>
                            <span>Недоступно</span>
                            <i class="fas fa-ban"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Услуги не найдены</h3>
                <p>В данном подкаталоге пока нет доступных услуг</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
