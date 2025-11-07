@extends('layouts.client')

@section('content')
            <!-- Services Page -->
            <div id="services" class="page">
                <div class="content-section">
                    <h2 class="page-title" id="servicesTitle">{{ $subCatalog->name }} — услуги</h2>
                    <div class="services-list" id="servicesList">
                        @forelse($services as $service)
                            <div class="service-item">
                                <div class="service-info">
                                    <h3>{{ $service->name }}</h3>
                                    @if($service->description)
                                        <p style="color: #666; font-size: 0.9rem;">{{ $service->description }}</p>
                                    @endif
                                    <div class="small text-muted">
                                        Доступно врачей: {{ $service->schedules->count() }}
                                    </div>
                                    @if($service->schedules->count())
                                        <div class="mt-2">
                                            @foreach($service->schedules as $schedule)
                                                <span class="badge bg-light text-dark me-1 mb-1">{{ $schedule->user->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="service-price">
                                    {{ $service->formatted_price }}
                                    <div class="mt-2">
                                        <a href="{{ route('service.booking', $service) }}" class="btn btn-primary btn-sm">Записаться</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info">Услуги не найдены</div>
                        @endforelse
                    </div>
                </div>
            </div>
@endsection
