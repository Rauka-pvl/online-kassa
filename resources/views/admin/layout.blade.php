<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Админ-панель - {{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ Storage::url('icons/back.jpg') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">


    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .sidebar .nav-link {
            color: #6c757d;
            padding: 0.75rem 1rem;
            border-radius: 0;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef;
            color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .content-wrapper {
            min-height: calc(100vh - 56px);
        }
        .stats-card {
            border-left: 4px solid #007bff;
        }
        .stats-card.success {
            border-left-color: #28a745;
        }
        .stats-card.info {
            border-left-color: #17a2b8;
        }
        .stats-card.warning {
            border-left-color: #ffc107;
        }
        .staff-toast-container {
            position: fixed;
            top: 72px;
            right: 16px;
            z-index: 1080;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 360px;
        }
        .staff-toast {
            background: #fff;
            border-left: 4px solid #0d6efd;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            border-radius: 8px;
            padding: 12px 14px;
            cursor: pointer;
            animation: staffToastIn .2s ease;
        }
        .staff-toast.is-cancelled {
            border-left-color: #dc3545;
        }
        .staff-toast-title {
            font-weight: 700;
            margin-bottom: 4px;
        }
        .staff-toast-body {
            font-size: 0.9rem;
            color: #495057;
        }
        @keyframes staffToastIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: none; }
        }
        .appointment-highlight {
            animation: highlightPulse 1.6s ease 2;
            background-color: #fff3cd !important;
        }
        @keyframes highlightPulse {
            0%, 100% { background-color: #fff; }
            50% { background-color: #fff3cd; }
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                    <img src="{{ Storage::url('icons/logo.png') }}" alt="Logo" height="30" class="me-2">
                    Админ-панель
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="staffNotifToggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Уведомления
                                <span id="staffNotifBadge" class="badge bg-danger rounded-pill d-none">0</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0" id="staffNotifMenu" style="min-width: 320px;">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <strong>Уведомления</strong>
                                    <button type="button" class="btn btn-link btn-sm p-0" id="staffNotifReadAll">Прочитать все</button>
                                </div>
                                <div id="staffNotifList">
                                    <div class="dropdown-item-text text-muted">Нет непрочитанных</div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/') }}" target="_blank">
                                Перейти на сайт
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Выйти
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    📊 Панель управления
                                </a>
                            </li>
                            @if (Auth::user()->role == 1)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.catalogs*') ? 'active' : '' }}" href="{{ route('admin.catalogs') }}">
                                        📁 Каталоги
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.subcatalogs*') ? 'active' : '' }}" href="{{ route('admin.subcatalogs') }}">
                                        📂 Подкаталоги
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.services*') ? 'active' : '' }}" href="{{ route('admin.services') }}">
                                        🛠️ Услуги
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                                        👥 Пользователи
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.schedules*') ? 'active' : '' }}" href="{{ route('admin.schedules') }}">
                                    📅 Графики работы
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.appointments*') ? 'active' : '' }}" href="{{ route('admin.appointments') }}">
                                    📋 Записи
                                </a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                                    📈 Отчеты
                                </a>
                            </li> --}}
                        </ul>
                    </div>
                </nav>

                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-wrapper">
                    <div class="py-4">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </div>
    <div id="staffToastContainer" class="staff-toast-container" aria-live="polite"></div>
    <script>
        (function () {
            const pollUrl = @json(route('admin.notifications.poll'));
            const readAllUrl = @json(route('admin.notifications.read-all'));
            const readUrlTemplate = @json(url('/admin/notifications/__ID__/read'));
            const appointmentsUrl = @json(route('admin.appointments'));
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const storageKey = 'staff_notif_last_id';
            const badge = document.getElementById('staffNotifBadge');
            const list = document.getElementById('staffNotifList');
            const toasts = document.getElementById('staffToastContainer');
            let lastId = parseInt(localStorage.getItem(storageKey) || '0', 10);
            let initialized = false;

            function appointmentLink(item) {
                const params = new URLSearchParams();
                if (item.appointment_id) params.set('highlight', item.appointment_id);
                if (item.appointment_date) params.set('date', item.appointment_date);
                return appointmentsUrl + '?' + params.toString();
            }

            function markRead(id) {
                return fetch(readUrlTemplate.replace('__ID__', id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
            }

            function renderUnread(items, count) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : String(count);
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                if (!items.length) {
                    list.innerHTML = '<div class="dropdown-item-text text-muted">Нет непрочитанных</div>';
                    return;
                }

                list.innerHTML = items.map(function (item) {
                    return '<a class="dropdown-item py-2" href="' + appointmentLink(item) + '" data-notif-id="' + item.id + '">' +
                        '<div class="fw-semibold">' + item.title + '</div>' +
                        '<div class="small text-muted">' + item.body + '</div>' +
                        '</a>';
                }).join('');
            }

            function showToast(item) {
                const el = document.createElement('div');
                el.className = 'staff-toast' + (item.type === 'booking_cancelled' ? ' is-cancelled' : '');
                el.innerHTML = '<div class="staff-toast-title">' + item.title + '</div>' +
                    '<div class="staff-toast-body">' + item.body + '</div>';
                el.addEventListener('click', function () {
                    markRead(item.id).finally(function () {
                        window.location.href = appointmentLink(item);
                    });
                });
                toasts.appendChild(el);
                setTimeout(function () {
                    el.remove();
                }, 5000);
            }

            async function poll() {
                try {
                    const response = await fetch(pollUrl + '?after_id=' + lastId, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) return;
                    const data = await response.json();
                    renderUnread(data.unread || [], data.unread_count || 0);

                    if (!initialized) {
                        lastId = data.latest_id || lastId;
                        localStorage.setItem(storageKey, String(lastId));
                        initialized = true;
                        return;
                    }

                    if ((data.latest_id || 0) < lastId) {
                        lastId = data.latest_id || 0;
                    }

                    (data.notifications || []).forEach(function (item) {
                        showToast(item);
                        lastId = Math.max(lastId, item.id);
                    });
                    localStorage.setItem(storageKey, String(lastId));
                } catch (e) {}
            }

            list.addEventListener('click', function (e) {
                const link = e.target.closest('[data-notif-id]');
                if (link) {
                    markRead(link.getAttribute('data-notif-id'));
                }
            });

            document.getElementById('staffNotifReadAll').addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                fetch(readAllUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                }).then(function () { poll(); });
            });

            poll();
            setInterval(poll, 8000);
        })();
    </script>
    @stack('scripts')
</body>
</html>
