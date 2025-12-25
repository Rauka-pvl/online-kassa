@extends('layouts.client')

@php
    $firstSubCatalog = $subCatalogs->first();
    $catalog = $firstSubCatalog ? $firstSubCatalog->catalog : null;
@endphp

@section('breadcrumb')
    @if($catalog)
        <li class="breadcrumb-item">
            <a href="{{ route('catalog') }}">{{ $catalog->name }}</a>
        </li>
    @endif
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-folder-open"></i> Подкаталоги
    </li>
@endsection

@section('content')
<div class="subcatalog-page">
    <div class="page-header">
        <h1 class="page-title-main">
            @if($catalog)
                {{ $catalog->name }}
            @else
                Подкаталоги
            @endif
        </h1>
        <p class="page-subtitle">Выберите интересующий вас подкаталог</p>
    </div>

    <div class="subcatalogs-container">
        @forelse($subCatalogs as $subCatalog)
            <a href="{{ route('services', ['id' => $subCatalog->id]) }}" class="subcatalog-card-modern">
                <div class="subcatalog-card-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="subcatalog-card-content">
                    <h3 class="subcatalog-card-title">{{ $subCatalog->name }}</h3>
                    @php
                        $servicesCount = $subCatalog->services()->where('is_active', true)->count();
                    @endphp
                    @if($servicesCount > 0)
                        <p class="subcatalog-card-count">
                            {{ $servicesCount }} {{ $servicesCount == 1 ? 'услуга доступна' : ($servicesCount < 5 ? 'услуги доступны' : 'услуг доступно') }}
                        </p>
                    @else
                        <p class="subcatalog-card-count text-muted">Услуги пока не добавлены</p>
                    @endif
                </div>
                <div class="subcatalog-card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Подкаталоги не найдены</h3>
                <p>Обратитесь к администратору для добавления подкаталогов</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
