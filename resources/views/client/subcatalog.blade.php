@extends('layouts.client')

@section('content')
            <!-- Subcatalog Page -->
            <div id="subcatalogPage" class="page">
                <div class="content-section">
                    <h2 class="page-title" id="subcatalogTitle">Подкаталог</h2>
                    <div class="sub-catalog-list" id="subCatalogList">
                        @foreach ($subCatalogs as $subCatalog)
                            <a href="{{ route('services', ['id' => $subCatalog->id]) }}" style="color: #333333;">
                                <div class="sub-catalog-item">
                                    <div class="sub-catalog-info">
                                        <h3>{{ $subCatalog->name }}</h3>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
@endsection
