<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [ClientController::class, 'main'])->name('main');
Route::get('/catalog', [ClientController::class, 'catalog'])->name('catalog');
Route::get('/sub-catalog/{id}', [ClientController::class, 'subCatalog'])->name('sub-catalog');
Route::get('/services/{id}', [ClientController::class, 'services'])->name('services');
Route::get('/about', [ClientController::class, 'about'])->name('about');
Route::get('/contacts', [ClientController::class, 'contacts'])->name('contacts');

Auth::routes();

// Админка
require __DIR__ . '/admin.php';
