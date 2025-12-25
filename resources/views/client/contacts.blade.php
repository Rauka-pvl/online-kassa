@extends('layouts.client')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="fas fa-phone"></i> Контакты
    </li>
@endsection

@section('content')
<div class="contacts-page">
    <div class="page-header">
        <h1 class="page-title-main">Контакты</h1>
        <p class="page-subtitle">Свяжитесь с нами удобным для вас способом</p>
    </div>

    <div class="contacts-content">
        <div class="contact-grid">
            <div class="contact-item">
                <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <h3>Адрес</h3>
                    <p>г. Павлодар, ул. Машхура Жусупа, 20/1</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                <div>
                    <h3>Email</h3>
                    <a href="mailto:ask.med@mail.ru">ask.med@mail.ru</a>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon"><i class="fas fa-clock"></i></div>
                <div>
                    <h3>Режим работы</h3>
                    <p>Пн-ВС: 8:00-20:00</p>
                </div>
            </div>

            <div class="contact-item contact-item-full">
                <div class="contact-item-header">
                    <div class="contact-icon"><i class="fas fa-phone"></i></div>
                    <h3>Телефон</h3>
                </div>
                <div class="contact-tel_list">
                    <a href="tel:+77051484470" class="tel-item">
                        <i class="fas fa-phone"></i>
                        <span>+7‒705‒148‒44‒70</span>
                    </a>
                    <a href="tel:+77776001000" class="tel-item">
                        <i class="fas fa-phone"></i>
                        <span>+7-777-600-10-00</span>
                    </a>
                    <a href="tel:+77182663326" class="tel-item">
                        <i class="fas fa-phone"></i>
                        <span>+7 (7182) 66‒33‒26</span>
                    </a>
                    <a href="tel:+77182663327" class="tel-item">
                        <i class="fas fa-phone"></i>
                        <span>+7 (7182) 66‒33‒27</span>
                    </a>
                    <a href="tel:+77182663328" class="tel-item">
                        <i class="fas fa-phone"></i>
                        <span>+7 (7182) 66‒33‒28</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1882.8068006452247!2d76.95174195236774!3d52.289857861502746!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x42f8352d4eef42bd%3A0x2b5264ecda22a486!2zQS5TLksuTUVEINCc0LXQtNC40YbQuNC90YHQutC40Lkg0YbQtdC90YLRgA!5e0!3m2!1sru!2skz!4v1757418898725!5m2!1sru!2skz" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</div>
@endsection
