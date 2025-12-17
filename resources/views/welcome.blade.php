<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A.S.K. MED - Медицинский центр</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ Storage::url('icons/back.jpg') }}">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #EDF6F9 0%, #6FABF6 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
            font-weight: bold;
            color: #E31F24;
            text-decoration: none;
        }

        .logo i {
            font-size: 2rem;
            color: #E31F24;
        }

        .search-container {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #EDF6F9;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            background: #FFFFFF;
        }

        .search-input:focus {
            border-color: #6FABF6;
            box-shadow: 0 0 0 3px rgba(111, 171, 246, 0.2);
        }

        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: #6FABF6;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            color: #FFFFFF;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: #E31F24;
        }

        .nav-menu {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: #6FABF6;
            color: #FFFFFF;
        }

        .mobile-menu {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #E31F24;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: rgba(237, 246, 249, 0.3);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .breadcrumb-content {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
        }

        .back-btn {
            background: #6FABF6;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            color: #FFFFFF;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #E31F24;
        }

        /* Main Content */
        .main-content {
            background: #FFFFFF;
            border-radius: 20px;
            margin: 2rem 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .hero {
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #6FABF6 0%, #E31F24 100%);
            color: #FFFFFF;
            text-align: center;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .content-section {
            padding: 2rem;
        }

        .page-title {
            font-size: 2rem;
            color: #E31F24;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .category-card {
            background: #FFFFFF;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid #EDF6F9;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-color: #6FABF6;
        }

        .category-icon {
            font-size: 3rem;
            color: #E31F24;
            margin-bottom: 1rem;
        }

        .category-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .category-desc {
            color: #666;
            font-size: 0.9rem;
        }

        /* Services List */
        .services-list {
            display: grid;
            gap: 1rem;
            margin-top: 2rem;
        }

        .service-item {
            background: #EDF6F9;
            padding: 1.5rem;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(111, 171, 246, 0.2);
        }

        .service-item:hover {
            background: #6FABF6;
            color: #FFFFFF;
            transform: translateX(10px);
            border-color: #6FABF6;
        }

        .service-info h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .service-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #E31F24;
        }

        .service-item:hover .service-price {
            color: #FFFFFF;
        }

        .pay-btn {
            background: #6FABF6;
            color: #FFFFFF;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .pay-btn:hover {
            background: #E31F24;
            transform: scale(1.05);
        }

        /* About & Contact Pages */
        .info-card {
            background: #EDF6F9;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border-left: 4px solid #E31F24;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #EDF6F9;
            border-radius: 10px;
            border: 1px solid rgba(111, 171, 246, 0.3);
        }

        .contact-icon {
            font-size: 1.5rem;
            color: #E31F24;
        }

        .map-container {
            width: 100%;
            height: 300px;
            background: #EDF6F9;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 2rem;
            color: #666;
            border: 2px solid #6FABF6;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: #FFFFFF;
            padding: 3rem 0 1rem;
            margin-top: 3rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            color: #6FABF6;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .footer-section p, .footer-section a {
            color: #bdc3c7;
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: block;
        }

        .footer-section a:hover {
            color: #6FABF6;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #6FABF6;
            color: #FFFFFF;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #E31F24;
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 1rem;
            text-align: center;
            color: #95a5a6;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
            }

            .nav-menu {
                display: none;
                width: 100%;
                flex-direction: column;
                gap: 1rem;
            }

            .nav-menu.active {
                display: flex;
            }

            .mobile-menu {
                display: block;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .categories-grid {
                grid-template-columns: 1fr;
            }

            .service-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
            }

            .hero {
                padding: 2rem 1rem;
            }

            .content-section {
                padding: 1rem;
            }

            .category-card {
                padding: 1.5rem;
            }
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="#" class="logo" onclick="showPage('home')">
                    <i class="fas fa-heartbeat"></i>
                    A.S.K. MED
                </a>

                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Поиск врачей и услуг...">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <nav class="nav-menu" id="navMenu">
                    <a href="#" class="nav-link active" onclick="showPage('home')">Главная</a>
                    <a href="#" class="nav-link" onclick="showPage('catalog')">Каталог</a>
                    <a href="#" class="nav-link" onclick="showPage('about')">О центре</a>
                    <a href="#" class="nav-link" onclick="showPage('contacts')">Контакты</a>
                </nav>

                <div class="mobile-menu" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </div>
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

    <!-- Main Content -->
    <main class="container">
        <div class="main-content">
            <!-- Home Page -->
            <div id="homePage" class="page">
                <div class="hero">
                    <h1>A.S.K. MED</h1>
                    <p>Современный медицинский центр с онлайн оплатой услуг</p>
                </div>

                <div class="content-section">
                    <h2 class="page-title">Наши направления</h2>
                    <div class="categories-grid">
                        <div class="category-card" onclick="showCatalog('therapy')">
                            <div class="category-icon"><i class="fas fa-stethoscope"></i></div>
                            <h3 class="category-title">Терапия</h3>
                            <p class="category-desc">Общая терапия, диагностика, консультации</p>
                        </div>
                        <div class="category-card" onclick="showCatalog('cardiology')">
                            <div class="category-icon"><i class="fas fa-heartbeat"></i></div>
                            <h3 class="category-title">Кардиология</h3>
                            <p class="category-desc">Диагностика и лечение заболеваний сердца</p>
                        </div>
                        <div class="category-card" onclick="showCatalog('neurology')">
                            <div class="category-icon"><i class="fas fa-brain"></i></div>
                            <h3 class="category-title">Неврология</h3>
                            <p class="category-desc">Лечение заболеваний нервной системы</p>
                        </div>
                        <div class="category-card" onclick="showCatalog('dermatology')">
                            <div class="category-icon"><i class="fas fa-hand-holding-medical"></i></div>
                            <h3 class="category-title">Дерматология</h3>
                            <p class="category-desc">Диагностика и лечение кожных заболеваний</p>
                        </div>
                        <div class="category-card" onclick="showCatalog('surgery')">
                            <div class="category-icon"><i class="fas fa-cut"></i></div>
                            <h3 class="category-title">Хирургия</h3>
                            <p class="category-desc">Хирургические операции и процедуры</p>
                        </div>
                        <div class="category-card" onclick="showCatalog('diagnostics')">
                            <div class="category-icon"><i class="fas fa-x-ray"></i></div>
                            <h3 class="category-title">Диагностика</h3>
                            <p class="category-desc">УЗИ, рентген, лабораторные исследования</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catalog Page -->
            <div id="catalogPage" class="page hidden">
                <div class="content-section">
                    <h2 class="page-title">Каталог услуг</h2>
                    <div class="categories-grid">
                        <div class="category-card" onclick="showSubcatalog('therapy')">
                            <div class="category-icon"><i class="fas fa-stethoscope"></i></div>
                            <h3 class="category-title">Терапия</h3>
                            <p class="category-desc">Консультации терапевта, общая диагностика</p>
                        </div>
                        <div class="category-card" onclick="showSubcatalog('cardiology')">
                            <div class="category-icon"><i class="fas fa-heartbeat"></i></div>
                            <h3 class="category-title">Кардиология</h3>
                            <p class="category-desc">ЭКГ, эхокардиография, консультации кардиолога</p>
                        </div>
                        <div class="category-card" onclick="showSubcatalog('neurology')">
                            <div class="category-icon"><i class="fas fa-brain"></i></div>
                            <h3 class="category-title">Неврология</h3>
                            <p class="category-desc">ЭЭГ, консультации невролога, МРТ головного мозга</p>
                        </div>
                        <div class="category-card" onclick="showSubcatalog('diagnostics')">
                            <div class="category-icon"><i class="fas fa-x-ray"></i></div>
                            <h3 class="category-title">Диагностика</h3>
                            <p class="category-desc">УЗИ, рентген, анализы крови и мочи</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subcatalog Page -->
            <div id="subcatalogPage" class="page hidden">
                <div class="content-section">
                    <h2 class="page-title" id="subcatalogTitle">Подкаталог</h2>
                    <div class="services-list" id="servicesList">
                        <!-- Services will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- About Page -->
            <div id="aboutPage" class="page hidden">
                <div class="content-section">
                    <h2 class="page-title">О медицинском центре A.S.K. MED</h2>

                    <div class="info-card">
                        <h3>Наша миссия</h3>
                        <p>Медицинский центр A.S.K. MED предоставляет качественные медицинские услуги с использованием современного оборудования и инновационных технологий. Мы стремимся обеспечить каждому пациенту индивидуальный подход и высокий уровень медицинского обслуживания.</p>
                    </div>

                    <div class="info-card">
                        <h3>Наши преимущества</h3>
                        <p>• Современное медицинское оборудование<br>
                        • Квалифицированные специалисты<br>
                        • Удобная онлайн запись и оплата<br>
                        • Комфортные условия пребывания<br>
                        • Индивидуальный подход к каждому пациенту</p>
                    </div>

                    <div class="info-card">
                        <h3>Лицензии и сертификаты</h3>
                        <p>Медицинский центр A.S.K. MED имеет все необходимые лицензии на осуществление медицинской деятельности. Наши специалисты регулярно повышают квалификацию и участвуют в профессиональных конференциях.</p>
                    </div>
                </div>
            </div>

            <!-- Contacts Page -->
            <div id="contactsPage" class="page hidden">
                <div class="content-section">
                    <h2 class="page-title">Контакты</h2>

                    <div class="contact-grid">
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h3>Адрес</h3>
                                <p>г. Павлодар, ул. Медицинская, 15</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div>
                                <h3>Телефон</h3>
                                <p>+7 (7182) 55-55-55</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <h3>Email</h3>
                                <p>info@askmed.kz</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <h3>Режим работы</h3>
                                <p>Пн-Пт: 8:00-20:00<br>Сб-Вс: 9:00-18:00</p>
                            </div>
                        </div>
                    </div>

                    <div class="map-container">
                        <i class="fas fa-map" style="font-size: 3rem; margin-right: 1rem; color: #6FABF6;"></i>
                        <span>Интерактивная карта</span>
                    </div>
                </div>
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
                        <a href="#" class="social-link">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-telegram"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Контакты</h3>
                    <p><i class="fas fa-map-marker-alt"></i> г. Павлодар, ул. Медицинская, 15</p>
                    <p><i class="fas fa-phone"></i> +7 (7182) 55-55-55</p>
                    <p><i class="fas fa-envelope"></i> info@askmed.kz</p>
                </div>

                <div class="footer-section">
                    <h3>Услуги</h3>
                    <a href="#">Терапия</a>
                    <a href="#">Кардиология</a>
                    <a href="#">Неврология</a>
                    <a href="#">Диагностика</a>
                </div>

                <div class="footer-section">
                    <h3>Информация</h3>
                    <a href="#">О центре</a>
                    <a href="#">Врачи</a>
                    <a href="#">Цены</a>
                    <a href="#">Онлайн запись</a>
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
            showPage('home');
        });
    </script>
</body>
</html>
