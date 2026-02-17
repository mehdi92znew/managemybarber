<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('super_admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('owner')) {
        return redirect()->route('owner.dashboard');
    } elseif ($user->hasRole('barber')) {
        return redirect()->route('barber.dashboard');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Super Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('shops', \App\Http\Controllers\Admin\AdminShopController::class);
    Route::patch('/shops/{shop}/suspend', [\App\Http\Controllers\Admin\AdminShopController::class, 'suspend'])->name('admin.shops.suspend');
    Route::patch('/shops/{shop}/activate', [\App\Http\Controllers\Admin\AdminShopController::class, 'activate'])->name('admin.shops.activate');
});

// Shop Owner Routes
Route::prefix('owner')->middleware(['auth', 'role:owner', 'shop.active'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Owner\OwnerDashboardController::class, 'index'])->name('owner.dashboard');
    Route::resource('barbers', \App\Http\Controllers\Owner\BarberController::class, ['as' => 'owner']);
    Route::resource('services', \App\Http\Controllers\Owner\ServiceController::class, ['as' => 'owner']);
    Route::resource('customers', \App\Http\Controllers\Owner\CustomerController::class, ['as' => 'owner']);
    Route::resource('bills', \App\Http\Controllers\Owner\BillController::class, ['as' => 'owner']);
    Route::resource('barber-payouts', \App\Http\Controllers\Owner\BarberPayoutController::class, ['as' => 'owner']);
    Route::get('/barber-report', [\App\Http\Controllers\Owner\BarberReportController::class, 'index'])->name('owner.barber-report');
    
    // Calendar & Appointments
    Route::get('/calendar', [\App\Http\Controllers\Owner\AppointmentController::class, 'index'])->name('owner.calendar');
    Route::get('/calendar/daily', [\App\Http\Controllers\Owner\AppointmentController::class, 'daily'])->name('owner.calendar.daily');
    Route::get('/appointments/list', [\App\Http\Controllers\Owner\AppointmentController::class, 'list'])->name('owner.appointments.list');
    Route::get('/appointments/events', [\App\Http\Controllers\Owner\AppointmentController::class, 'events'])->name('owner.appointments.events');
    Route::post('/appointments', [\App\Http\Controllers\Owner\AppointmentController::class, 'store'])->name('owner.appointments.store');
    Route::patch('/appointments/{appointment}', [\App\Http\Controllers\Owner\AppointmentController::class, 'update'])->name('owner.appointments.update');
    Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Owner\AppointmentController::class, 'destroy'])->name('owner.appointments.destroy');
});

// Barber Routes
Route::prefix('barber')->middleware(['auth', 'role:barber', 'shop.active'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Barber\BarberDashboardController::class, 'index'])->name('barber.dashboard');
    
    // Calendar
    Route::get('/calendar', [\App\Http\Controllers\Barber\AppointmentController::class, 'index'])->name('barber.calendar');
    Route::get('/appointments/events', [\App\Http\Controllers\Barber\AppointmentController::class, 'events'])->name('barber.appointments.events');
    Route::post('/appointments', [\App\Http\Controllers\Barber\AppointmentController::class, 'store'])->name('barber.appointments.store');
    Route::patch('/appointments/{appointment}', [\App\Http\Controllers\Barber\AppointmentController::class, 'update'])->name('barber.appointments.update');
    Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Barber\AppointmentController::class, 'destroy'])->name('barber.appointments.destroy');
    
    // Customers
    Route::resource('customers', \App\Http\Controllers\Owner\CustomerController::class, ['as' => 'barber']);

    // Payouts
    Route::get('/payouts', [\App\Http\Controllers\Barber\BarberPayoutController::class, 'index'])->name('barber.payouts.index');
});

Route::post('/payments/create-intent', [\App\Http\Controllers\PaymentController::class, 'createIntent'])->name('payments.create-intent')->middleware(['auth', 'verified']);
Route::get('language/{locale}', [\App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');

Route::post('/payments/verify', [\App\Http\Controllers\PaymentController::class, 'verify'])->name('payments.verify')->middleware(['auth', 'verified']);

Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
