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
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Shops Management
    Route::resource('shops', \App\Http\Controllers\Admin\AdminShopController::class);
    Route::patch('/shops/{shop}/approve', [\App\Http\Controllers\Admin\AdminShopController::class, 'approve'])->name('shops.approve');
    Route::patch('/shops/{shop}/suspend', [\App\Http\Controllers\Admin\AdminShopController::class, 'suspend'])->name('shops.suspend');
    Route::patch('/shops/{shop}/activate', [\App\Http\Controllers\Admin\AdminShopController::class, 'activate'])->name('shops.activate');
    Route::patch('/shops/{shop}/subscription', [\App\Http\Controllers\Admin\AdminShopController::class, 'updateSubscription'])->name('shops.subscription.update');
    Route::post('/shops/{shop}/impersonate', [\App\Http\Controllers\Admin\AdminShopController::class, 'impersonate'])->name('shops.impersonate');

    // Users Management
    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleBlock'])->name('users.toggle');
    
    Route::get('/logs', [\App\Http\Controllers\Admin\AdminLogController::class, 'index'])->name('logs.index');
    
    // Platform Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\AdminSettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [\App\Http\Controllers\Admin\AdminSettingController::class, 'update'])->name('settings.update');
});

Route::post('/admin/leave-impersonation', [\App\Http\Controllers\Admin\AdminShopController::class, 'leaveImpersonation'])
    ->middleware('auth')
    ->name('admin.impersonate.leave');

// Shop Owner Routes
Route::middleware(['auth', 'role:owner', 'shop.active'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Owner\OwnerDashboardController::class, 'index'])->name('dashboard');
    Route::resource('barbers', \App\Http\Controllers\Owner\BarberController::class);
    Route::resource('services', \App\Http\Controllers\Owner\ServiceController::class);
    Route::resource('customers', \App\Http\Controllers\Owner\CustomerController::class);
    Route::resource('bills', \App\Http\Controllers\Owner\BillController::class);
    Route::resource('barber-payouts', \App\Http\Controllers\Owner\BarberPayoutController::class);
    Route::get('/barber-report', [\App\Http\Controllers\Owner\BarberReportController::class, 'index'])->name('barber-report');
    Route::resource('notes', \App\Http\Controllers\Owner\NoteController::class);
    Route::resource('cash-drawer', \App\Http\Controllers\Owner\CashDrawerController::class)->only(['index', 'store', 'update']);
    
    // Calendar & Appointments
    Route::get('/calendar', [\App\Http\Controllers\Owner\AppointmentController::class, 'index'])->name('calendar');
    Route::get('/calendar/daily', [\App\Http\Controllers\Owner\AppointmentController::class, 'daily'])->name('calendar.daily');
    Route::get('/appointments/list', [\App\Http\Controllers\Owner\AppointmentController::class, 'list'])->name('appointments.list');
    Route::get('/appointments/events', [\App\Http\Controllers\Owner\AppointmentController::class, 'events'])->name('appointments.events');
    Route::post('/appointments', [\App\Http\Controllers\Owner\AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/appointments/{appointment}', [\App\Http\Controllers\Owner\AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Owner\AppointmentController::class, 'destroy'])->name('appointments.destroy');
});

// Barber Routes
Route::middleware(['auth', 'role:barber', 'shop.active'])
    ->prefix('barber')
    ->name('barber.')
    ->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Barber\BarberDashboardController::class, 'index'])->name('dashboard');
    
    // Calendar
    Route::get('/calendar', [\App\Http\Controllers\Barber\AppointmentController::class, 'index'])->name('calendar');
    Route::get('/appointments/list', [\App\Http\Controllers\Barber\AppointmentController::class, 'list'])->name('appointments.list');
    Route::get('/appointments/events', [\App\Http\Controllers\Barber\AppointmentController::class, 'events'])->name('appointments.events');
    Route::post('/appointments', [\App\Http\Controllers\Barber\AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/appointments/{appointment}', [\App\Http\Controllers\Barber\AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Barber\AppointmentController::class, 'destroy'])->name('appointments.destroy');
    
    // Customers
    Route::resource('customers', \App\Http\Controllers\Owner\CustomerController::class);

    // Notes
    Route::get('/notes', [\App\Http\Controllers\Barber\NoteController::class, 'index'])->name('notes.index');

    // Payouts
    Route::get('/payouts', [\App\Http\Controllers\Barber\BarberPayoutController::class, 'index'])->name('payouts.index');
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
