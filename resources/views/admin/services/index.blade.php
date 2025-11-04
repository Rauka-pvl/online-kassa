{{-- resources/views/admin/services/index.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Услуги</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            + Добавить услугу
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
                        <th>Название</th>
                        <th>Подкаталог</th>
                        <th>Цена</th>
                        <th>Длительность</th>
                        <th>Статус</th>
                        <th>Создана</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>{{ $service->id }}</td>
                            <td>
                                <strong>{{ $service->name }}</strong>
                                @if($service->description)
                                    <br><small class="text-muted">{{ Str::limit($service->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $service->subCatalog->name }}</span>
                                <br><small class="text-muted">{{ $service->subCatalog->catalog->name }}</small>
                            </td>
                            <td>
                                <strong class="text-success">{{ $service->formatted_price }}</strong>
                            </td>
                            <td>
                                @if($service->duration)
                                    <span class="badge bg-info">{{ $service->duration }} мин</span>
                                @else
                                    <span class="badge bg-secondary">Без времени</span>
                                @endif
                            </td>
                            <td>
                                @if($service->is_active)
                                    <span class="badge bg-success">Активна</span>
                                @else
                                    <span class="badge bg-danger">Неактивна</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $service->created_at->format('d.m.Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.services.edit', $service) }}"
                                       class="btn btn-sm btn-outline-primary" title="Редактировать">
                                        ✏️
                                    </a>
                                    <form action="{{ route('admin.services.destroy', $service) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Вы уверены, что хотите удалить эту услугу?')">
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
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <p>Услуги не найдены</p>
                                    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                                        Создать первую услугу
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="d-flex justify-content-center">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
