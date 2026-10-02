<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A.S.K. MED — Медицинский центр</title>
    <link rel="icon" type="image/jpeg" href="{{ Storage::url('icons/back.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite([
        'resources/sass/app.scss',
        'resources/js/app.js',
        'resources/js/client.js',
        'resources/css/app.css',
        'resources/css/client.css'
    ])
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('main') }}" class="logo">
                    <img src="{{ Storage::url('icons/logo.png') }}" alt="A.S.K. MED">
                </a>

                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Врач или услуга" autocomplete="off">
                    <button type="button" class="search-btn" aria-label="Найти">
                        <i class="fas fa-search"></i>
                    </button>
                    <div id="searchResults" class="search-results"></div>
                </div>

                <div class="mobile-menu" onclick="toggleMobileMenu()" role="button" aria-label="Меню">
                    <i class="fas fa-bars"></i>
                </div>

                <nav class="nav-menu" id="navMenu">
                    <a href="{{ route('main') }}" class="nav-link @if (request()->routeIs('main')) active @endif">Главная</a>
                    <a href="{{ route('catalog') }}" class="nav-link @if (request()->routeIs('catalog') || request()->routeIs('sub-catalog') || request()->routeIs('services') || request()->routeIs('service.booking') || request()->routeIs('doctor.show')) active @endif">Каталог</a>
                    <a href="{{ route('booking.find') }}" class="nav-link @if (request()->routeIs('booking.find') || request()->routeIs('booking.show')) active @endif">Моя запись</a>
                    <a href="{{ route('about') }}" class="nav-link @if (request()->routeIs('about')) active @endif">О центре</a>
                    <a href="{{ route('contacts') }}" class="nav-link @if (request()->routeIs('contacts')) active @endif">Контакты</a>
                    <a href="{{ route('catalog') }}" class="nav-link header-cta">Записаться</a>
                </nav>
            </div>
        </div>
    </header>

    @hasSection('breadcrumb')
    <div class="breadcrumb-wrapper" id="breadcrumbWrapper">
        <div class="container">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item">
                        <a href="{{ route('main') }}">Главная</a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
    </div>
    @endif

    <main class="main-wrapper">
        <div class="container">
            <div class="main-content">
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section footer-brand">
                    <h3>A.S.K. MED</h3>
                    <p>Медицинский центр в Павлодаре</p>
                    <p>Диагностика, лечение и запись к врачу без очереди</p>
                    <div class="social-links">
                        <a href="https://api.whatsapp.com/send?phone=77051484470&text=%D0%97%D0%B4%D1%80%D0%B0%D0%B2%D1%81%D1%82%D0%B2%D1%83%D0%B9%D1%82%D0%B5,%20%D0%BC%D0%B5%D0%BD%D1%8F%20%D0%B8%D0%BD%D1%82%D0%B5%D1%80%D0%B5%D1%81%D1%83%D0%B5%D1%82" target="_blank" rel="noopener" class="social-link" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.instagram.com/askmed__pvl/" target="_blank" rel="noopener" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/channel/UCLd2VCuPVvwSTYWnRQwpwQQ" target="_blank" rel="noopener" class="social-link" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Контакты</h3>
                    <p>г. Павлодар, ул. Машхура Жусупа, 20/1</p>
                    <a href="tel:+77051484470">+7 705 148 44 70</a>
                    <a href="tel:+77776001000">+7 777 600 10 00</a>
                    <a href="tel:+77182663326">+7 (7182) 66-33-26</a>
                    <a href="mailto:ask.med@mail.ru">ask.med@mail.ru</a>
                </div>

                <div class="footer-section">
                    <h3>Услуги</h3>
                    @foreach (App\Models\Catalog::get() as $catalog)
                        <a href="{{ route('sub-catalog', ['id' => $catalog->id]) }}">{{ $catalog->name }}</a>
                    @endforeach
                </div>

                <div class="footer-section">
                    <h3>Пациентам</h3>
                    <a href="{{ route('catalog') }}">Каталог услуг</a>
                    <a href="{{ route('booking.find') }}">Моя запись</a>
                    <a href="{{ route('about') }}">О центре</a>
                    <a href="{{ route('contacts') }}">Контакты</a>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} A.S.K. MED. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            document.getElementById('navMenu').classList.toggle('active');
        }
    </script>

    @stack('scripts')
</body>
</html>
