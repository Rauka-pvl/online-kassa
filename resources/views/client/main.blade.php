@extends('layouts.client')

@section('content')
@php
    $icons = ['fa-heartbeat', 'fa-user-md', 'fa-flask', 'fa-notes-medical', 'fa-hand-holding-medical', 'fa-spa'];
@endphp
<div class="home-page">
    <section class="hero-section">
        <div class="hero-content">
            <span class="hero-kicker">Павлодар · ул. Машхура Жусупа, 20/1</span>
            <h1 class="hero-title">
                <span class="hero-title-main">Запись к врачу без очереди</span>
                <span class="hero-title-sub">A.S.K. MED — медицинский центр</span>
            </h1>
            <p class="hero-description">Выберите услугу, свободное время и оставьте контакты. Оплата — в клинике, регистратор подтвердит приём.</p>
            <div class="hero-features">
                <div class="hero-feature">
                    <i class="fas fa-clock"></i>
                    <span>7 дней в неделю, 8:00–20:00</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-calendar-check"></i>
                    <span>Онлайн-запись</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-user-md"></i>
                    <span>Опытные специалисты</span>
                </div>
            </div>
            <div class="hero-actions">
                <a href="{{ route('catalog') }}" class="hero-cta">
                    <span>Смотреть услуги</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="{{ route('booking.find') }}" class="hero-cta is-ghost">
                    <span>Моя запись</span>
                    <i class="fas fa-search"></i>
                </a>
            </div>
        </div>
    </section>

    <section class="categories-section">
        <div class="section-header">
            <span class="section-kicker">Направления</span>
            <h2 class="section-title">Выберите, к кому записаться</h2>
            <p class="section-subtitle">Каталог услуг клиники — от диагностики до узких специалистов</p>
        </div>
        <div class="categories-grid">
            @forelse($catalogs as $catalog)
                <a href="{{ route('catalog') }}#catalog-{{ $catalog->id }}" class="category-card-modern">
                    <div class="category-card-icon">
                        <i class="fas {{ $icons[$loop->index % count($icons)] }}"></i>
                    </div>
                    <div class="category-card-content">
                        <h3 class="category-card-title">{{ $catalog->name }}</h3>
                        @if($catalog->description)
                            <p class="category-card-desc">{{ \Illuminate\Support\Str::limit($catalog->description, 100) }}</p>
                        @endif
                        <span class="category-card-link">
                            Открыть <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Каталоги пока не добавлены</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="why-choose-section">
        <div class="section-header">
            <span class="section-kicker">Почему мы</span>
            <h2 class="section-title">Клиника, в которую возвращаются</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-microscope"></i>
                </div>
                <h3 class="feature-title">Современная диагностика</h3>
                <p class="feature-desc">Лаборатория, УЗИ, рентген и функциональные исследования в одном здании</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="feature-title">Врачи разных профилей</h3>
                <p class="feature-desc">Терапия, хирургия, гинекология, кардиология, ЛОР и другие специальности</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="feature-title">Без онлайн-оплаты</h3>
                <p class="feature-desc">Записались на сайте — оплатили в клинике. Регистратор подтвердит визит</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3 class="feature-title">В центре Павлодара</h3>
                <p class="feature-desc">ул. Машхура Жусупа, 20/1. Парковка, лифт, работаем без выходных</p>
            </div>
        </div>
    </section>
</div>
@endsection
