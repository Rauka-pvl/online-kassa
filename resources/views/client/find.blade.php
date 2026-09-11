@extends('layouts.client')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-search"></i> Моя запись
    </li>
@endsection

@section('content')
<div class="booking-page">
    <div class="page-header">
        <h1 class="page-title-main">Моя запись</h1>
        <p class="page-subtitle">Введите телефон и код записи с экрана подтверждения, например ASK-12</p>
    </div>

    <div class="booking-container">
        <div class="booking-form-wrapper">
            <form action="{{ route('booking.lookup') }}" method="POST" class="booking-form">
                @csrf
                <div class="form-section">
                    <div class="form-group">
                        <label class="form-label" for="find_phone">Телефон</label>
                        <input type="tel" name="client_phone" id="find_phone" class="form-control-modern"
                               placeholder="+7 (___) ___-__-__" value="{{ old('client_phone') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="find_code">Код записи</label>
                        <input type="text" name="code" id="find_code" class="form-control-modern"
                               placeholder="ASK-12" value="{{ old('code') }}" required>
                    </div>
                    @if ($errors->any())
                        <div class="form-errors">
                            <div class="alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <div>{{ $errors->first() }}</div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary-large">
                        <span>Найти запись</span>
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInput = document.getElementById('find_phone');
            if (window.IMask && phoneInput) {
                IMask(phoneInput, { mask: '+{7}(000)000-00-00' });
            }
        });
    </script>
@endpush
