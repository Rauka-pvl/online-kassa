<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель — A.S.K. MED</title>
    <link rel="icon" type="image/jpeg" href="{{ Storage::url('icons/back.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #152536;
            --brand: #d21f28;
            --teal: #1a6b74;
            --paper: #f3eee6;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: Manrope, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(900px 420px at 100% 0%, rgba(210, 31, 40, 0.12), transparent 55%),
                radial-gradient(700px 380px at 0% 100%, rgba(26, 107, 116, 0.14), transparent 50%),
                var(--paper);
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .login-shell {
            width: min(920px, 100%);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: #fffdfa;
            border: 1px solid rgba(21, 37, 54, 0.08);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(21, 37, 54, 0.1);
        }
        .login-brand {
            padding: 48px 40px;
            background: #101c28;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 520px;
        }
        .login-brand img { height: 46px; width: auto; }
        .login-brand h1 {
            font-family: Fraunces, Georgia, serif;
            font-size: 2.3rem;
            font-weight: 560;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin: 28px 0 12px;
        }
        .login-brand p { color: #b7c2cc; max-width: 18rem; }
        .login-meta { font-size: 0.85rem; color: #8b97a4; }
        .login-form {
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-form h2 {
            font-size: 1.45rem;
            margin-bottom: 8px;
        }
        .login-form > p {
            color: #5d6d7c;
            margin-bottom: 28px;
        }
        label {
            display: block;
            font-size: 0.86rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .field { margin-bottom: 16px; }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid rgba(21, 37, 54, 0.1);
            border-radius: 14px;
            font: inherit;
            outline: none;
            background: #fff;
        }
        input:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 4px rgba(26, 107, 116, 0.12);
        }
        .is-invalid { border-color: var(--brand) !important; }
        .invalid-feedback {
            display: block;
            margin-top: 6px;
            color: var(--brand);
            font-size: 0.84rem;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 6px 0 22px;
            color: #5d6d7c;
            font-size: 0.92rem;
        }
        button {
            width: 100%;
            border: 0;
            border-radius: 999px;
            padding: 13px 18px;
            background: var(--ink);
            color: #fff;
            font: inherit;
            font-weight: 750;
            cursor: pointer;
        }
        button:hover { background: var(--brand); }
        .back {
            display: inline-block;
            margin-top: 18px;
            color: #5d6d7c;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back:hover { color: var(--brand); }
        @media (max-width: 800px) {
            .login-shell { grid-template-columns: 1fr; }
            .login-brand { min-height: auto; padding: 28px 24px; }
            .login-form { padding: 28px 24px 32px; }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <aside class="login-brand">
            <div>
                <img src="{{ Storage::url('icons/logo.png') }}" alt="A.S.K. MED">
                <h1>Кабинет сотрудников</h1>
                <p>Записи пациентов, графики врачей и подтверждение приёмов.</p>
            </div>
            <div class="login-meta">A.S.K. MED · Павлодар</div>
        </aside>
        <section class="login-form">
            <h2>Вход</h2>
            <p>Логин и пароль выдаёт администратор клиники.</p>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field">
                    <label for="login">Логин</label>
                    <input id="login" type="text" class="@error('login') is-invalid @enderror" name="login" value="{{ old('login') }}" required autocomplete="username" autofocus>
                    @error('login')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">Пароль</label>
                    <input id="password" type="password" class="@error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Запомнить меня
                </label>
                <button type="submit">Войти</button>
            </form>
            <a class="back" href="{{ url('/') }}">← На сайт клиники</a>
        </section>
    </div>
</body>
</html>
