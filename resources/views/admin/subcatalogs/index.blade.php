{{-- resources/views/admin/subcatalogs/index.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Подкаталоги</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.subcatalogs.create') }}" class="btn btn-primary">
            + Добавить подкаталог
        </a>
    </div>
</div>

<!-- Поиск и фильтры -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.subcatalogs') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Поиск по названию..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="catalog_filter" class="form-select">
                    <option value="">Все каталоги</option>
                    @foreach($catalogs as $catalog)
                        <option value="{{ $catalog->id }}" {{ request('catalog_filter') == $catalog->id ? 'selected' : '' }}>
                            {{ $catalog->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Применить</button>
                </div>
            </div>
            @if(request()->anyFilled(['search', 'catalog_filter', 'sort_by', 'sort_order']))
                <div class="col-md-12">
                    <a href="{{ route('admin.subcatalogs') }}" class="btn btn-outline-secondary btn-sm">Сбросить</a>
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
                        <th>Название</th>
                        <th>Каталог</th>
                        <th>Услуги</th>
                        <th>Создан</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subCatalogs as $subCatalog)
                        <tr>
                            <td>{{ $subCatalog->id }}</td>
                            <td>
                                <strong>{{ $subCatalog->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $subCatalog->catalog->name }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $subCatalog->services_count ?? $subCatalog->services->count() }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $subCatalog->created_at->format('d.m.Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.subcatalogs.edit', $subCatalog) }}"
                                       class="btn btn-sm btn-outline-primary" title="Редактировать">
                                        ✏️
                                    </a>
                                    <form action="{{ route('admin.subcatalogs.destroy', $subCatalog) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Удалить подкаталог? Услуги, привязанные только к нему, нужно сначала перенести или удалить.')">
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
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <p>Подкаталоги не найдены</p>
                                    <a href="{{ route('admin.subcatalogs.create') }}" class="btn btn-primary">
                                        Создать первый подкаталог
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subCatalogs->hasPages())
            <div class="d-flex justify-content-center">
                {{ $subCatalogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
