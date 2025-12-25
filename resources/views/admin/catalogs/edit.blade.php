{{-- resources/views/admin/catalogs/edit.blade.php --}}
@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Редактировать каталог</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.catalogs') }}" class="btn btn-outline-secondary">
            ← Назад к каталогам
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.catalogs.update', $catalog) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Название каталога *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $catalog->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4">{{ old('description', $catalog->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Изображение</label>
                        @if($catalog->image)
                            <div class="mb-2">
                                <img src="{{ Storage::url($catalog->image) }}" alt="{{ $catalog->name }}"
                                     class="img-thumbnail" style="max-width: 200px;">
                                <p class="small text-muted mt-1">Текущее изображение</p>
                            </div>
                        @endif
                        <input type="file" class="form-control @error('image') is-invalid @enderror"
                               id="image" name="image" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Максимальный размер файла: 2MB. Форматы: JPEG, PNG, JPG, GIF</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.catalogs') }}" class="btn btn-outline-secondary">
                            Отмена
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Обновить каталог
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
