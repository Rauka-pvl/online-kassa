@extends('layouts.client')

@section('content')
<!-- Catalog Page -->
            <div id="catalogPage" class="page">
                <div class="content-section">
                    <h2 class="page-title">Каталог услуг</h2>
                    <div class="categories-grid">
                    @foreach($catalogs as $catalog)
                        <a class="category-card" href="{{ route('sub-catalog', ['id' => $catalog->id]) }}">
                            <div class="category-icon"><i class="fas fa-stethoscope"></i></div>
                            <h3 class="category-title">{{ $catalog->name }}</h3>
                            <p class="category-desc">{{ $catalog->description }}</p>
                        </a>
                    @endforeach
                </div>
                </div>
            </div>

            <!-- Subcatalog Page -->
            <div id="subcatalogPage" class="page hidden">
                <div class="content-section">
                    <h2 class="page-title" id="subcatalogTitle">Подкаталог</h2>
                    <div class="services-list" id="servicesList">
                        <!-- Services will be populated by JavaScript -->
                    </div>
                </div>
            </div>
@endsection
