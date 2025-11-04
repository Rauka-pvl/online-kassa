{{-- resources/views/admin/subcatalogs/edit.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать подкаталог</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.subcatalogs') }}" class="btn btn-outline-secondary">
            ← Назад к подкаталогам
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.subcatalogs.update', $subcatalog) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="catalog_id" class="form-label">Каталог *</label>
                        <select class="form-select @error('catalog_id') is-invalid @enderror"
                                id="catalog_id" name="catalog_id" required>
                            <option value="">Выберите каталог</option>
                            @foreach($catalogs as $catalog)
                                <option value="{{ $catalog->id }}"
                                        {{ old('catalog_id', $subcatalog->catalog_id) == $catalog->id ? 'selected' : '' }}>
                                    {{ $catalog->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('catalog_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Название подкаталога *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $subcatalog->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.subcatalogs') }}" class="btn btn-outline-secondary">
                            Отмена
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Обновить подкаталог
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
