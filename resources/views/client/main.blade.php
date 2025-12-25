@extends('layouts.client')

@section('content')
<div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">
                <span class="hero-title-main">A.S.K. MED</span>
                <span class="hero-title-sub">Медицинский центр</span>
            </h1>
            <p class="hero-description">Современная медицина с заботой о вашем здоровье</p>
            <div class="hero-features">
                <div class="hero-feature">
                    <i class="fas fa-clock"></i>
                    <span>Работаем 7 дней в неделю</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-calendar-check"></i>
                    <span>Онлайн запись</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-user-md"></i>
                    <span>Опытные специалисты</span>
                </div>
            </div>
            <a href="{{ route('catalog') }}" class="hero-cta">
                <span>Посмотреть услуги</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="section-header">
            <h2 class="section-title">Наши направления</h2>
            <p class="section-subtitle">Выберите интересующее вас направление</p>
        </div>
        <div class="categories-grid">
            @forelse($catalogs as $catalog)
                <a href="{{ route('catalog') }}#catalog-{{ $catalog->id }}" class="category-card-modern">
                    <div class="category-card-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="category-card-content">
                        <h3 class="category-card-title">{{ $catalog->name }}</h3>
                        @if($catalog->description)
                            <p class="category-card-desc">{{ \Illuminate\Support\Str::limit($catalog->description, 100) }}</p>
                        @endif
                        <span class="category-card-link">
                            Подробнее <i class="fas fa-arrow-right"></i>
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

    <!-- Why Choose Us Section -->
    <section class="why-choose-section">
        <div class="section-header">
            <h2 class="section-title">Почему выбирают нас</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-microscope"></i>
                </div>
                <h3 class="feature-title">Современное оборудование</h3>
                <p class="feature-desc">Используем передовые технологии диагностики и лечения</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="feature-title">Опытные врачи</h3>
                <p class="feature-desc">Высококвалифицированные специалисты с многолетним опытом</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="feature-title">Индивидуальный подход</h3>
                <p class="feature-desc">Персональное внимание к каждому пациенту</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3 class="feature-title">Удобное расположение</h3>
                <p class="feature-desc">Центр города, удобная парковка</p>
            </div>
        </div>
    </section>
</div>
@endsection
