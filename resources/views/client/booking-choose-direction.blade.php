@extends('layouts.client')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-directions"></i> Выберите направление
    </li>
@endsection

@section('content')
<div class="booking-page">
    <div class="page-header">
        <span class="section-kicker">Онлайн-запись</span>
        <h1 class="page-title-main">{{ $service->name }}</h1>
        <p class="page-subtitle">Услуга доступна в нескольких направлениях — выберите нужное, чтобы увидеть подходящих врачей.</p>
    </div>

    <div class="services-container">
        @foreach($service->subCatalogs as $sub)
            @php
                $catalogName = optional($sub->catalog)->name;
                $label = trim(($catalogName ? $catalogName . ' → ' : '') . $sub->name);
            @endphp
            <div class="service-card-modern">
                <div class="service-card-header">
                    <div class="service-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="service-header-content">
                        <h3 class="service-title">{{ $label }}</h3>
                        <p class="service-description">{{ $service->formatted_price }}</p>
                    </div>
                </div>
                <div class="service-card-footer">
                    <a href="{{ route('service.booking', ['service' => $service, 'sub_catalog' => $sub->id]) }}"
                       class="service-book-btn">
                        <span>Выбрать</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
