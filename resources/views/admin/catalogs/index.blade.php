@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Каталоги</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.catalogs.create') }}" class="btn btn-primary">
            + Добавить каталог
        </a>
    </div>
</div>

<!-- Поиск и сортировка -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.catalogs') }}" class="row g-3">
            <div class="col-md-6">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Поиск по названию или описанию..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="sort_by" class="form-select">
                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>Сортировка: ID</option>
                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>По названию</option>
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>По дате</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort_order" class="form-select">
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>По убыванию</option>
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>По возрастанию</option>
                </select>
            </div>
            <div class="col-md-1">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Применить</button>
                </div>
            </div>
            @if(request()->anyFilled(['search', 'sort_by', 'sort_order']))
                <div class="col-md-12">
                    <a href="{{ route('admin.catalogs') }}" class="btn btn-outline-secondary btn-sm">Сбросить</a>
                </div>
            @endif
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
                        {{-- <th>Изображение</th> --}}
                        <th>Название</th>
                        <th>Описание</th>
                        <th>Подкаталоги</th>
                        <th>Создан</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($catalogs as $catalog)
                        <tr>
                            <td>{{ $catalog->id }}</td>
                            {{-- <td>
                                @if($catalog->image)
                                    <img src="{{ Storage::url($catalog->image) }}" alt="{{ $catalog->name }}"
                                         class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                @else
                                    <span class="text-muted">Нет изображения</span>
                                @endif
                            </td> --}}
                            <td>
                                <strong>{{ $catalog->name }}</strong>
                            </td>
                            <td>
                                <span class="text-muted">
                                    {{ \Illuminate\Support\Str::limit($catalog->description, 50) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $catalog->sub_catalogs_count ?? $catalog->subCatalogs->count() }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $catalog->created_at->format('d.m.Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.catalogs.edit', $catalog) }}"
                                       class="btn btn-sm btn-outline-primary" title="Редактировать">
                                        ✏️
                                    </a>
                                    <form action="{{ route('admin.catalogs.destroy', $catalog) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Вы уверены, что хотите удалить этот каталог? Все связанные подкаталоги и услуги также будут удалены.')">
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
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <p>Каталоги не найдены</p>
                                    <a href="{{ route('admin.catalogs.create') }}" class="btn btn-primary">
                                        Создать первый каталог
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($catalogs->hasPages())
            <div class="d-flex justify-content-center">
                {{ $catalogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
