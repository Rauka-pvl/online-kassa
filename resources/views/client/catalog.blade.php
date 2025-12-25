@extends('layouts.client')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-folder"></i> Каталог
    </li>
@endsection

@section('content')
<div class="catalog-page">
    <div class="page-header">
        <h1 class="page-title-main">Каталог услуг</h1>
        <p class="page-subtitle">Выберите интересующее вас направление медицины</p>
    </div>

    <div class="catalogs-container">
        @forelse($catalogs as $catalog)
            <div class="catalog-card" id="catalog-{{ $catalog->id }}">
                <div class="catalog-card-header">
                    <div class="catalog-icon-wrapper">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="catalog-header-content">
                        <h2 class="catalog-title">{{ $catalog->name }}</h2>
                        @if($catalog->description)
                            <p class="catalog-description">{{ $catalog->description }}</p>
                        @endif
                    </div>
                </div>

                @php
                    $subCatalogs = $catalog->subCatalogs;
                @endphp

                @if($subCatalogs->count() > 0)
                    <div class="subcatalogs-grid">
                        @foreach($subCatalogs as $subCatalog)
                            <a href="{{ route('services', ['id' => $subCatalog->id]) }}" class="subcatalog-card">
                                <div class="subcatalog-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h3 class="subcatalog-title">{{ $subCatalog->name }}</h3>
                                @php
                                    $servicesCount = $subCatalog->services()->where('is_active', true)->count();
                                @endphp
                                @if($servicesCount > 0)
                                    <span class="subcatalog-badge">{{ $servicesCount }} {{ $servicesCount == 1 ? 'услуга' : ($servicesCount < 5 ? 'услуги' : 'услуг') }}</span>
                                @endif
                                <span class="subcatalog-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty-subcatalogs">
                        <i class="fas fa-inbox"></i>
                        <p>Подкаталоги пока не добавлены</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Каталоги пока не добавлены</h3>
                <p>Обратитесь к администратору для добавления каталогов услуг</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
