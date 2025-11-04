<?php
// routes/admin.php
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Models\Appointment;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Catalogs
    Route::get('/catalogs', [AdminController::class, 'catalogs'])->name('catalogs');
    Route::get('/catalogs/create', [AdminController::class, 'createCatalog'])->name('catalogs.create');
    Route::post('/catalogs', [AdminController::class, 'storeCatalog'])->name('catalogs.store');
    Route::get('/catalogs/{catalog}/edit', [AdminController::class, 'editCatalog'])->name('catalogs.edit');
    Route::put('/catalogs/{catalog}', [AdminController::class, 'updateCatalog'])->name('catalogs.update');
    Route::delete('/catalogs/{catalog}', [AdminController::class, 'destroyCatalog'])->name('catalogs.destroy');

    // Sub Catalogs
    Route::get('/subcatalogs', [AdminController::class, 'subCatalogs'])->name('subcatalogs');
    Route::get('/subcatalogs/create', [AdminController::class, 'createSubCatalog'])->name('subcatalogs.create');
    Route::post('/subcatalogs', [AdminController::class, 'storeSubCatalog'])->name('subcatalogs.store');
    Route::get('/subcatalogs/{subcatalog}/edit', [AdminController::class, 'editSubCatalog'])->name('subcatalogs.edit');
    Route::put('/subcatalogs/{subcatalog}', [AdminController::class, 'updateSubCatalog'])->name('subcatalogs.update');
    Route::delete('/subcatalogs/{subcatalog}', [AdminController::class, 'destroySubCatalog'])->name('subcatalogs.destroy');

    // Services
    Route::get('/services', [AdminController::class, 'services'])->name('services');
    Route::get('/services/create', [AdminController::class, 'createService'])->name('services.create');
    Route::post('/services', [AdminController::class, 'storeService'])->name('services.store');
    Route::get('/services/{service}/edit', [AdminController::class, 'editService'])->name('services.edit');
    Route::put('/services/{service}', [AdminController::class, 'updateService'])->name('services.update');
    Route::delete('/services/{service}', [AdminController::class, 'destroyService'])->name('services.destroy');

    // Users (Doctors & Registrars)
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

    // Schedules
    Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
    Route::get('/schedules/create', [AdminController::class, 'createSchedule'])->name('schedules.create');
    Route::post('/schedules', [AdminController::class, 'storeSchedule'])->name('schedules.store');
    Route::get('/schedules/{schedule}/edit', [AdminController::class, 'editSchedule'])->name('schedules.edit');
    Route::put('/schedules/{schedule}', [AdminController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [AdminController::class, 'destroySchedule'])->name('schedules.destroy');

    Route::get('/schedules/{schedule}/services', [AdminController::class, 'getServicesAdmin'])->name('schedules.services');

    // NEW: Schedule details and day view
    Route::get('/schedules/{schedule}/show', [AdminController::class, 'showScheduleDetails'])->name('schedules.show');
    Route::get('/schedules/{schedule}/day/{date}', [AdminController::class, 'getScheduleDayView'])->name('schedules.day-view');
    Route::get('/schedules/{schedule}/dayBooked/{date}', [AdminController::class, 'getTimeSlotsAdmin'])->name('schedules.dayBooked');

    // // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments');
    Route::post('/appointment/create/{schedule}', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointment/complete/{appointment}', [AppointmentController::class, 'completeAppointment'])->name('appointments.complete');
    // Route::get('/appointments/{appointment}/edit', [AdminController::class, 'editAppointment'])->name('appointments.edit');
    Route::post('/appointment/update/{appointment}', [AppointmentController::class, 'updateAppointment'])->name('appointments.update');
    Route::get('/appointment/get/{appointment}', [AppointmentController::class, 'getAppointments'])->name('appointments.get');
    Route::delete('/appointment/delete/{appointment}', [AppointmentController::class, 'destroyAppointment'])->name('appointments.destroy');


    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [AdminController::class, 'generateReport'])->name('reports.generate');
});
