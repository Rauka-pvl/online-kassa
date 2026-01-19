<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/main', [ClientController::class, 'main'])->name('main');
Route::get('/catalog', [ClientController::class, 'catalog'])->name('catalog');
Route::get('/sub-catalog/{id}', [ClientController::class, 'subCatalog'])->name('sub-catalog');
Route::get('/services/{id}', [ClientController::class, 'services'])->name('services');
Route::get('/service/{service}/booking', [ClientController::class, 'booking'])->name('service.booking');

// Public booking + confirmation (payment mock)
Route::post('/booking', [PublicBookingController::class, 'store'])->name('booking.store');
Route::get('/booking/confirm', [PublicBookingController::class, 'confirm'])->name('booking.confirm');
Route::get('/api/schedules/{schedule}/slots', [PublicBookingController::class, 'slots'])->name('api.schedules.slots');

// Live search API
Route::get('/api/search', [SearchController::class, 'index'])->name('api.search');
Route::get('/about', [ClientController::class, 'about'])->name('about');
Route::get('/contacts', [ClientController::class, 'contacts'])->name('contacts');

Auth::routes();

// Админка
require __DIR__ . '/admin.php';
