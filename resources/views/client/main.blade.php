@extends('layouts.client')

@section('content')
        <!-- Home Page -->
        <div id="homePage" class="page">
            <div class="hero">
                <h1>A.S.K. MED</h1>
                <p>Современный медицинский центр с онлайн оплатой услуг</p>
            </div>

            <div class="content-section">
                <h2 class="page-title">Наши направления</h2>
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
@endsection
