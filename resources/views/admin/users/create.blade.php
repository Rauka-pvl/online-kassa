{{-- resources/views/admin/users/create.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Создать пользователя</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
            ← Назад к пользователям
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Имя *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="login" class="form-label">Логин *</label>
                            <input type="text" class="form-control @error('login') is-invalid @enderror"
                                   id="login" name="login" value="{{ old('login') }}" required>
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Пароль *</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Подтверждение пароля *</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Роль *</label>
                            <select class="form-select @error('role') is-invalid @enderror"
                                    id="role" name="role" required>
                                <option value="">Выберите роль</option>
                                <option value="2" {{ old('role') == '2' ? 'selected' : '' }}>Бухгалтер</option>
                                <option value="3" {{ old('role') == '3' ? 'selected' : '' }}>Регистратор</option>
                                <option value="4" {{ old('role') == '4' ? 'selected' : '' }}>Врач</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="specialization" class="form-label">Специализация</label>
                            <input type="text" class="form-control @error('specialization') is-invalid @enderror"
                                   id="specialization" name="specialization" value="{{ old('specialization') }}">
                            @error('specialization')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Для врачей</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Статус</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active"
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Пользователь активен
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                            Отмена
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Создать пользователя
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
