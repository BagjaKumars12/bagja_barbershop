<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Kasir\KasirDashboardController;
use App\Http\Controllers\Kasir\KasirTransactionController;
use App\Http\Controllers\Kasir\KasirBookingController;
use App\Http\Controllers\Kasir\KasirCustomerController;
use App\Http\Controllers\Kasir\KasirBarberController;
use App\Http\Controllers\Kasir\KasirServiceController;
use App\Http\Controllers\Kasir\KasirHistoryController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerBookingController;
use App\Http\Controllers\Owner\OwnerCustomerController;
use App\Http\Controllers\Owner\OwnerBarberController;
use App\Http\Controllers\Owner\OwnerServiceController;
use App\Http\Controllers\Owner\OwnerLaporanController;
use App\Http\Controllers\Owner\OwnerLogActivityController;

// Login Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // Admin Routes
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/barbers', [BarberController::class, 'index'])->name('barbers.index');
    Route::post('/barbers', [BarberController::class, 'store'])->name('barbers.store');
    Route::put('/barbers/{id}', [BarberController::class, 'update'])->name('barbers.update');
    Route::delete('/barbers/{id}', [BarberController::class, 'destroy'])->name('barbers.destroy');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy'])->name('bookings.destroy');
        // tambah route lainnya nanti
    });

    // Kasir Routes
    Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', [KasirDashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [KasirTransactionController::class, 'index'])->name('transactions');
    Route::post('/transactions', [KasirTransactionController::class, 'store'])->name('transactions.store');
    Route::get('/booking/{id}/total', [KasirTransactionController::class, 'getBookingTotal'])->name('booking.total');
    Route::post('/transactions/{bookingId}/pay', [KasirTransactionController::class, 'processPayment'])->name('transactions.pay');
    Route::get('/transactions/receipt/{id}', [KasirTransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::post('/customers', [KasirCustomerController::class, 'store'])->name('customers.store');
    Route::get('/bookings', [KasirBookingController::class, 'index'])->name('bookings.index');
    Route::put('/bookings/{id}/status', [KasirBookingController::class, 'updateStatus'])->name('bookings.updateStatus');
    Route::post('/bookings', [KasirBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/create', [KasirBookingController::class, 'create'])->name('bookings.create');
    Route::get('/customers', [KasirCustomerController::class, 'index'])->name('customers.index');
    Route::get('/barbers', [KasirBarberController::class, 'index'])->name('barbers');
    Route::get('/services', [KasirServiceController::class, 'index'])->name('services');
    Route::get('/history', [KasirHistoryController::class, 'index'])->name('history');
    });

    // Owner Routes
    Route::middleware(['role:owner'])->prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings.index');
        Route::put('/bookings/{id}/status', [OwnerBookingController::class, 'updateStatus'])->name('bookings.updateStatus');
        Route::get('/customers', [OwnerCustomerController::class, 'index'])->name('customers.index');
        Route::get('/barbers', [OwnerBarberController::class, 'index'])->name('barbers.index');
        Route::get('/services', [OwnerServiceController::class, 'index'])->name('services.index');
        Route::get('/history', [OwnerHistoryController::class, 'index'])->name('history');
        Route::get('/users', [OwnerDashboardController::class, 'users'])->name('users');
    });
}); 
// Default redirect
Route::get('/', function () {
    return redirect('/login');
});