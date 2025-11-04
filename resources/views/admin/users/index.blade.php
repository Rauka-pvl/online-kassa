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
