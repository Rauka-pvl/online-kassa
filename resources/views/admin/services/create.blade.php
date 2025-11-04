{{-- resources/views/admin/services/create.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Создать услугу</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.services') }}" class="btn btn-outline-secondary">
            ← Назад к услугам
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.services.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sub_catalog_id" class="form-label">Подкаталог *</label>
                            <select class="form-select @error('sub_catalog_id') is-invalid @enderror"
                                    id="sub_catalog_id" name="sub_catalog_id" required>
                                <option value="">Выберите подкаталог</option>
                                @foreach($subCatalogs as $subCatalog)
                                    <option value="{{ $subCatalog->id }}" {{ old('sub_catalog_id') == $subCatalog->id ? 'selected' : '' }}>
                                        {{ $subCatalog->catalog->name }} → {{ $subCatalog->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sub_catalog_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Название услуги *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Цена (₸) *</label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror"
                                   id="price" name="price" value="{{ old('price') }}" min="0" step="0.01" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="duration" class="form-label">Длительность (мин)</label>
                            <input type="number" class="form-control @error('duration') is-invalid @enderror"
                                   id="duration" name="duration" value="{{ old('duration') }}" min="1">
                            @error('duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Оставьте пустым, если услуга без времени</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Статус</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active"
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Услуга активна
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.services') }}" class="btn btn-outline-secondary">
                            Отмена
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Создать услугу
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
