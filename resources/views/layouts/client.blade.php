<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A.S.K. MED - Медицинский центр</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ Storage::url('icons/back.jpg') }}">

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
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('main') }}" class="logo">
                    {{-- <i class="fas fa-heartbeat"></i>
                    A.S.K. MED --}}
                    <img src="{{ Storage::url('icons/logo.png') }}" alt="A.S.K. MED">
                </a>

                <div class="search-container" style="position: relative;">
                    <input type="text" class="search-input" placeholder="Поиск врачей и услуг..." autocomplete="off">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    <div id="searchResults" class="search-results" style="position:absolute; top:100%; left:0; right:0; z-index:1000; display:none;"></div>
                </div>

                <div class="mobile-menu" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </div>

                <nav class="nav-menu" id="navMenu">
                    <a href="{{ route('main') }}" class="nav-link @if (request()->routeIs('main')) active @endif">Главная</a>
                    <a href="{{ route('catalog') }}" class="nav-link @if (request()->routeIs('catalog')) active @elseif (request()->routeIs('sub-catalog')) active @endif">Каталог</a>
                    <a href="{{ route('booking.find') }}" class="nav-link @if (request()->routeIs('booking.find') || request()->routeIs('booking.show')) active @endif">Моя запись</a>
                    <a href="{{ route('about') }}" class="nav-link @if (request()->routeIs('about')) active @endif">О центре</a>
                    <a href="{{ route('contacts') }}" class="nav-link @if (request()->routeIs('contacts')) active @endif">Контакты</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    @hasSection('breadcrumb')
    <div class="breadcrumb-wrapper" id="breadcrumbWrapper">
        <div class="container">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item">
                        <a href="{{ route('main') }}">
                            <i class="fas fa-home"></i> Главная
                        </a>
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>A.S.K. MED</h3>
                    <p>Современный медицинский центр</p>
                    <p>Качественные медицинские услуги</p>
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
                    <p><i class="fas fa-map-marker-alt"></i> г. Павлодар, ул. Машхура Жусупа, 20/1</p>
                    <p><i class="fas fa-phone"></i> +7‒705‒148‒44‒70</p>
                    <p><i class="fas fa-phone"></i> +7-777-600-10-00</p>
                    <p><i class="fas fa-phone"></i> +7 (7182) 66‒33‒26</p>
                    <p><i class="fas fa-phone"></i> +7 (7182) 66‒33‒27</p>
                    <p><i class="fas fa-phone"></i> +7 (7182) 66‒33‒28</p>
                    <a href="mailto:ask.med@mail.ru"><p><i class="fas fa-envelope"></i> ask.med@mail.ru</p></a>

                </div>

                <div class="footer-section">
                    <h3>Услуги</h3>
                    @foreach (App\Models\Catalog::get() as $catalog)
                        <a href="{{ route('sub-catalog', ['id' => $catalog->id]) }}">{{ $catalog->name }}</a>
                    @endforeach
                </div>

                <div class="footer-section">
                    <h3>Информация</h3>
                    <a href="{{ route('about') }}">О центре</a>
                    <a href="{{ route('contacts') }}">Контакты</a>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 A.S.K. MED. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }
    </script>

    @stack('scripts')
</body>
</html>
