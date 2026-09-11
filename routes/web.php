<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return 123;
//     // return redirect()->route('main');
// });
Route::get('/', [ClientController::class, 'main'])->name('main');
Route::get('/catalog', [ClientController::class, 'catalog'])->name('catalog');
Route::get('/sub-catalog/{id}', [ClientController::class, 'subCatalog'])->name('sub-catalog');
Route::get('/services/{id}', [ClientController::class, 'services'])->name('services');
Route::get('/service/{service}/booking', [ClientController::class, 'booking'])->name('service.booking');
Route::get('/doctor/{user}', [ClientController::class, 'doctor'])->name('doctor.show');

Route::post('/booking', [PublicBookingController::class, 'store'])->name('booking.store');
Route::get('/my-appointment', [PublicBookingController::class, 'findForm'])->name('booking.find');
Route::post('/my-appointment', [PublicBookingController::class, 'lookup'])->name('booking.lookup');
Route::get('/booking/{token}', [PublicBookingController::class, 'show'])->name('booking.show')->where('token', '[A-Za-z0-9]{32,64}');
Route::post('/booking/{token}/cancel', [PublicBookingController::class, 'cancel'])->name('booking.cancel')->where('token', '[A-Za-z0-9]{32,64}');
Route::get('/api/schedules/{schedule}/slots', [PublicBookingController::class, 'slots'])->name('api.schedules.slots');

// Live search API
Route::get('/api/search', [SearchController::class, 'index'])->name('api.search');
Route::get('/about', [ClientController::class, 'about'])->name('about');
Route::get('/contacts', [ClientController::class, 'contacts'])->name('contacts');

Auth::routes();

// Админка
require __DIR__ . '/admin.php';
