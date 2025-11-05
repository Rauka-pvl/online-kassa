{{-- resources/views/admin/users/index.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Пользователи</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            + Добавить пользователя
        </a>
    </div>
</div>

<!-- Поиск и фильтры -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Поиск по имени, логину, телефону, специализации..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="role_filter" class="form-select">
                    <option value="">Все роли</option>
                    <option value="2" {{ request('role_filter') == '2' ? 'selected' : '' }}>Бухгалтер</option>
                    <option value="3" {{ request('role_filter') == '3' ? 'selected' : '' }}>Регистратор</option>
                    <option value="4" {{ request('role_filter') == '4' ? 'selected' : '' }}>Врач</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status_filter" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="active" {{ request('status_filter') == 'active' ? 'selected' : '' }}>Активные</option>
                    <option value="inactive" {{ request('status_filter') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort_by" class="form-select">
                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>Сортировка: ID</option>
                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>По имени</option>
                    <option value="login" {{ request('sort_by') == 'login' ? 'selected' : '' }}>По логину</option>
                    <option value="role" {{ request('sort_by') == 'role' ? 'selected' : '' }}>По роли</option>
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>По дате</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort_order" class="form-select">
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                </select>
            </div>
            <div class="col-md-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Применить</button>
                    @if(request()->anyFilled(['search', 'role_filter', 'status_filter', 'sort_by', 'sort_order']))
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">Сбросить</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Логин</th>
                        <th>Роль</th>
                        <th>Специализация</th>
                        <th>Телефон</th>
                        <th>Статус</th>
                        <th>Создан</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>
                            <td>{{ $user->login }}</td>
                            <td>
                                @if($user->role == 1)
                                    <span class="badge bg-info">Админ</span>
                                @elseif($user->role == 2)
                                    <span class="badge bg-info">Бухгалтер</span>
                                @elseif($user->role == 3)
                                    <span class="badge bg-warning">Регистратор</span>
                                @elseif($user->role == 4)
                                    <span class="badge bg-warning">Врач</span>
                                @else
                                    <span class="badge bg-secondary">{{ $user->role }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->specialization)
                                    <small class="text-muted">{{ $user->specialization }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($user->phone)
                                    <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-danger">Неактивен</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $user->created_at->format('d.m.Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-primary" title="Редактировать">
                                        ✏️
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <p>Пользователи не найдены</p>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                        Создать первого пользователя
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
