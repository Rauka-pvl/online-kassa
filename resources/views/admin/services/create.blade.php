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
                            Отметьте «Основной» — он используется в хлебных крошках и поиске.
                        </p>
                        <div class="border rounded p-3 @error('sub_catalog_ids') border-danger @enderror"
                             style="max-height: 320px; overflow-y: auto;">
                            @php
                                $oldIds = collect(old('sub_catalog_ids', []))->map(fn ($id) => (int) $id);
                                $oldPrimary = (int) old('primary_sub_catalog_id', $oldIds->first());
                            @endphp
                            @forelse($subCatalogs as $subCatalog)
                                @php $checked = $oldIds->contains($subCatalog->id); @endphp
                                <div class="d-flex align-items-center gap-2 mb-2 service-subcatalog-row">
                                    <div class="form-check flex-grow-1 mb-0">
                                        <input class="form-check-input subcatalog-check"
                                               type="checkbox"
                                               name="sub_catalog_ids[]"
                                               value="{{ $subCatalog->id }}"
                                               id="sub_catalog_{{ $subCatalog->id }}"
                                               {{ $checked ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sub_catalog_{{ $subCatalog->id }}">
                                            {{ $subCatalog->catalog->name ?? '—' }} → {{ $subCatalog->name }}
                                        </label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input primary-radio"
                                               type="radio"
                                               name="primary_sub_catalog_id"
                                               value="{{ $subCatalog->id }}"
                                               id="primary_{{ $subCatalog->id }}"
                                               {{ $oldPrimary === (int) $subCatalog->id ? 'checked' : '' }}
                                               {{ $checked ? '' : 'disabled' }}>
                                        <label class="form-check-label small text-muted" for="primary_{{ $subCatalog->id }}">
                                            Основной
                                        </label>
                                    </div>
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

    function syncPrimaryRadios() {
        const checks = form.querySelectorAll('.subcatalog-check');
        let firstChecked = null;
        let primaryStillValid = false;
        const currentPrimary = form.querySelector('.primary-radio:checked');

        checks.forEach(function (cb) {
            const radio = form.querySelector('#primary_' + cb.value);
            if (!radio) return;
            radio.disabled = !cb.checked;
            if (cb.checked && !firstChecked) firstChecked = radio;
            if (cb.checked && currentPrimary && currentPrimary.value === cb.value) {
                primaryStillValid = true;
            }
        });

        if (!primaryStillValid && firstChecked) {
            firstChecked.checked = true;
        }
    }

    form.querySelectorAll('.subcatalog-check').forEach(function (cb) {
        cb.addEventListener('change', syncPrimaryRadios);
    });

    form.addEventListener('submit', function (e) {
        const checked = form.querySelectorAll('.subcatalog-check:checked');
        if (!checked.length) {
            e.preventDefault();
            alert('Выберите хотя бы один подкаталог.');
        }
    });

    syncPrimaryRadios();
});
</script>
@endpush
