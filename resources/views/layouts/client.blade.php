<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A.S.K. MED - Медицинский центр</title>
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

                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Поиск врачей и услуг...">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <div class="mobile-menu" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </div>

                <nav class="nav-menu" id="navMenu">
                    <a href="{{ route('main') }}" class="nav-link @if (request()->routeIs('main')) active @endif">Главная</a>
                    <a href="{{ route('catalog') }}" class="nav-link @if (request()->routeIs('catalog')) active @elseif (request()->routeIs('sub-catalog')) active @endif">Каталог</a>
                    <a href="{{ route('about') }}" class="nav-link @if (request()->routeIs('about')) active @endif">О центре</a>
                    <a href="{{ route('contacts') }}" class="nav-link @if (request()->routeIs('contacts')) active @endif">Контакты</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb" id="breadcrumb" style="display: none;">
        <div class="container">
            <div class="breadcrumb-content">
                <button class="back-btn" onclick="goBack()">
                    <i class="fas fa-arrow-left"></i> Назад
                </button>
                <span id="breadcrumbText"></span>
            </div>
        </div>
    </div>

    <main class="container">
        <div class="main-content">
            @yield('content')
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
                    <div class="social-links" style="display: flex; gap: 1rem; justify-content: center; /* или flex-start / flex-end по желанию */margin-top: 1rem;">
                        <a href="https://api.whatsapp.com/send?phone=77051484470&text=%D0%97%D0%B4%D1%80%D0%B0%D0%B2%D1%81%D1%82%D0%B2%D1%83%D0%B9%D1%82%D0%B5,%20%D0%BC%D0%B5%D0%BD%D1%8F%20%D0%B8%D0%BD%D1%82%D0%B5%D1%80%D0%B5%D1%81%D1%83%D0%B5%D1%82" target="__blank" class="social-link" style="width: 50px; height: 50px; border-radius: 50%; background: #EDF6F9; /* или свой цвет */ display: flex; align-items: center; justify-content: center; color: #0a66c2; /* цвет иконки */ font-size: 20px; border: 1px solid rgba(111, 171, 246, 0.3); transition: all 0.3s ease;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.instagram.com/askmed__pvl/" target="__blank" class="social-link" style="width: 50px; height: 50px; border-radius: 50%; background: #EDF6F9; /* или свой цвет */ display: flex; align-items: center; justify-content: center; color: #0a66c2; /* цвет иконки */ font-size: 20px; border: 1px solid rgba(111, 171, 246, 0.3); transition: all 0.3s ease;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/channel/UCLd2VCuPVvwSTYWnRQwpwQQ" target="__blank" class="social-link" style="width: 50px; height: 50px; border-radius: 50%; background: #EDF6F9; /* или свой цвет */ display: flex; align-items: center; justify-content: center; color: #0a66c2; /* цвет иконки */ font-size: 20px; border: 1px solid rgba(111, 171, 246, 0.3); transition: all 0.3s ease;">
                            <i class="fab fa-youtube"></i>
                        </a>
                        {{-- <a href="#" class="social-link" style="width: 50px; height: 50px; border-radius: 50%; background: #EDF6F9; /* или свой цвет */ display: flex; align-items: center; justify-content: center; color: #0a66c2; /* цвет иконки */ font-size: 20px; border: 1px solid rgba(111, 171, 246, 0.3); transition: all 0.3s ease;">
                            <i class="fab fa-telegram"></i>
                        </a> --}}
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
        // Navigation and page switching
        let currentPage = 'home';
        let navigationHistory = [];

        const services = {
            therapy: [
                { name: 'Консультация терапевта', price: '8000 ₸', description: 'Первичная консультация' },
                { name: 'Повторная консультация терапевта', price: '6000 ₸', description: 'Повторный прием' },
                { name: 'Справка для работы/учебы', price: '3000 ₸', description: 'Медицинская справка' },
                { name: 'Диспансеризация', price: '15000 ₸', description: 'Комплексное обследование' }
            ],
            cardiology: [
                { name: 'Консультация кардиолога', price: '12000 ₸', description: 'Консультация специалиста' },
                { name: 'ЭКГ', price: '4000 ₸', description: 'Электрокардиограмма' },
                { name: 'Эхокардиография', price: '8000 ₸', description: 'УЗИ сердца' },
                { name: 'Холтеровское мониторирование', price: '15000 ₸', description: 'Суточный мониторинг ЭКГ' }
            ],
            neurology: [
                { name: 'Консультация невролога', price: '10000 ₸', description: 'Консультация специалиста' },
                { name: 'ЭЭГ', price: '7000 ₸', description: 'Электроэнцефалограмма' },
                { name: 'Дуплексное сканирование сосудов', price: '9000 ₸', description: 'УЗИ сосудов головы и шеи' },
                { name: 'Консультация + ЭЭГ', price: '15000 ₸', description: 'Комплексное обследование' }
            ],
            diagnostics: [
                { name: 'УЗИ органов брюшной полости', price: '6000 ₸', description: 'Комплексное УЗИ' },
                { name: 'Рентген грудной клетки', price: '3500 ₸', description: 'Рентгенография' },
                { name: 'Общий анализ крови', price: '2000 ₸', description: 'Лабораторное исследование' },
                { name: 'Биохимический анализ крови', price: '4000 ₸', description: 'Расширенный анализ' }
            ]
        };

        function showPage(pageId) {
            // Hide all pages
            const pages = document.querySelectorAll('.page');
            pages.forEach(page => page.classList.add('hidden'));

            // Show selected page
            document.getElementById(pageId + 'Page').classList.remove('hidden');

            // Update navigation
            updateNavigation(pageId);
            currentPage = pageId;

            // Hide breadcrumb for main pages
            if (['home', 'catalog', 'about', 'contacts'].includes(pageId)) {
                document.getElementById('breadcrumb').style.display = 'none';
            }
        }

        function showCatalog(category) {
            navigationHistory.push(currentPage);
            showPage('catalog');
        }

        function showSubcatalog(category) {
            navigationHistory.push(currentPage);

            // Hide all pages
            const pages = document.querySelectorAll('.page');
            pages.forEach(page => page.classList.add('hidden'));

            // Show subcatalog page
            document.getElementById('subcatalogPage').classList.remove('hidden');

            // Update title and breadcrumb
            const titles = {
                therapy: 'Терапия',
                cardiology: 'Кардиология',
                neurology: 'Неврология',
                diagnostics: 'Диагностика'
            };

            document.getElementById('subcatalogTitle').textContent = titles[category];
            document.getElementById('breadcrumb').style.display = 'block';
            document.getElementById('breadcrumbText').textContent = titles[category];

            // Populate services
            const servicesList = document.getElementById('servicesList');
            servicesList.innerHTML = '';

            if (services[category]) {
                services[category].forEach(service => {
                    const serviceItem = document.createElement('div');
                    serviceItem.className = 'service-item';
                    serviceItem.innerHTML = `
                        <div class="service-info">
                            <h3>${service.name}</h3>
                            <p style="color: #666; font-size: 0.9rem;">${service.description}</p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span class="service-price">${service.price}</span>
                            <button class="pay-btn" onclick="payForService('${service.name}', '${service.price}')">Оплатить</button>
                        </div>
                    `;
                    servicesList.appendChild(serviceItem);
                });
            }
        }

        function updateNavigation(pageId) {
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => link.classList.remove('active'));

            // Find and activate current page link
            navLinks.forEach(link => {
                if (link.textContent.toLowerCase().includes(getPageTitle(pageId).toLowerCase())) {
                    link.classList.add('active');
                }
            });
        }

        function getPageTitle(pageId) {
            const titles = {
                home: 'главная',
                catalog: 'каталог',
                about: 'о центре',
                contacts: 'контакты'
            };
            return titles[pageId] || pageId;
        }

        function goBack() {
            if (navigationHistory.length > 0) {
                const previousPage = navigationHistory.pop();
                showPage(previousPage);
            } else {
                showPage('home');
            }
        }

        function toggleMobileMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }

        function payForService(serviceName, price) {
            alert(`Переход к оплате услуги: ${serviceName} - ${price}`);
            // Здесь будет интеграция с платёжной системой
        }

        // Search functionality
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    const results = performSearch(query);
                    showSearchResults(results, query);
                }
            }
        });

        document.querySelector('.search-btn').addEventListener('click', function() {
            const query = document.querySelector('.search-input').value.trim();
            if (query) {
                const results = performSearch(query);
                showSearchResults(results, query);
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // showPage('home');
        });
    </script>
</body>
</html>
