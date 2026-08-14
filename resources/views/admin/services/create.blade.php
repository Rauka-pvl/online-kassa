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
                <form action="{{ route('admin.services.store') }}" method="POST" id="serviceForm">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Название услуги *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Подкаталоги *</label>
                        <p class="text-muted small mb-2">
                            Можно привязать услугу к любому количеству подкаталогов.
                        </p>
                        <div class="border rounded p-3 @error('sub_catalog_ids') border-danger @enderror"
                             style="max-height: 320px; overflow-y: auto;">
                            @php
                                $oldIds = collect(old('sub_catalog_ids', []))->map(fn ($id) => (int) $id);
                            @endphp
                            @forelse($subCatalogs as $subCatalog)
                                <div class="form-check mb-2">
                                    <input class="form-check-input subcatalog-check"
                                           type="checkbox"
                                           name="sub_catalog_ids[]"
                                           value="{{ $subCatalog->id }}"
                                           id="sub_catalog_{{ $subCatalog->id }}"
                                           {{ $oldIds->contains($subCatalog->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sub_catalog_{{ $subCatalog->id }}">
                                        {{ $subCatalog->catalog->name ?? '—' }} → {{ $subCatalog->name }}
                                    </label>
                                </div>
                            @empty
                                <div class="text-muted">Сначала создайте подкаталоги</div>
                            @endforelse
                        </div>
                        @error('sub_catalog_ids')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('sub_catalog_ids.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('serviceForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        if (!form.querySelectorAll('.subcatalog-check:checked').length) {
            e.preventDefault();
            alert('Выберите хотя бы один подкаталог.');
        }
    });
});
</script>
@endpush
