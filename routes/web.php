<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $role = request()->user()->role;
    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'security' => redirect()->route('security.dashboard'),
        default => redirect()->route('user.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User Accounts Management
    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/registration/{id}/approve', [\App\Http\Controllers\Admin\AdminRegistrationController::class, 'approve'])->name('registration.approve');
    Route::post('/registration/{id}/reject', [\App\Http\Controllers\Admin\AdminRegistrationController::class, 'reject'])->name('registration.reject');
    
    // Approved Registrations & QR
    Route::get('/approved', [\App\Http\Controllers\Admin\AdminApprovedRegistrationController::class, 'index'])->name('approved.index');
    Route::get('/approved/{registration}/qr', [\App\Http\Controllers\Admin\AdminApprovedRegistrationController::class, 'generateQr'])->name('approved.qr');
    Route::post('/approved/{registration}/schedule', [\App\Http\Controllers\Admin\AdminApprovedRegistrationController::class, 'schedulePickup'])->name('approved.schedule');
    Route::post('/approved/{registration}/claim', [\App\Http\Controllers\Admin\AdminApprovedRegistrationController::class, 'markAsClaimed'])->name('approved.claim');

    // Sanctions
    Route::get('/sanctions', [\App\Http\Controllers\Admin\AdminSanctionController::class, 'index'])->name('sanctions.index');
    Route::post('/sanctions/{violation}', [\App\Http\Controllers\Admin\AdminSanctionController::class, 'store'])->name('sanctions.store');
    Route::post('/sanctions/{sanction}/resolve', [\App\Http\Controllers\Admin\AdminSanctionController::class, 'resolve'])->name('sanctions.resolve');
});

// Security Personnel Routes
Route::middleware(['auth', 'verified', 'role:security'])->prefix('security')->name('security.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Security\SecurityDashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [\App\Http\Controllers\Security\SecurityDashboardController::class, 'search'])->name('search');
    Route::get('/user-location/{user}', [\App\Http\Controllers\Security\SecurityDashboardController::class, 'getUserLocation'])->name('user.location');
    
    Route::get('/violation/create', [\App\Http\Controllers\Security\ViolationController::class, 'create'])->name('violation.create');
    Route::post('/violation', [\App\Http\Controllers\Security\ViolationController::class, 'store'])->name('violation.store');
});

// Regular User Routes
Route::middleware(['auth', 'verified', 'role:vehicle_user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\User\UserDashboardController::class, 'index'])->name('dashboard');

    // Vehicle Registration routes
    Route::get('/registration/create', [\App\Http\Controllers\RegistrationController::class, 'create'])->name('registration.create');
    Route::post('/registration', [\App\Http\Controllers\RegistrationController::class, 'store'])->name('registration.store');

    // Profile photo
    Route::post('/profile/photo', [\App\Http\Controllers\User\UserProfileController::class, 'uploadPhoto'])->name('profile.photo.upload');
    Route::delete('/profile/photo', [\App\Http\Controllers\User\UserProfileController::class, 'removePhoto'])->name('profile.photo.remove');

    // Live Location Tracking
    Route::post('/location', [\App\Http\Controllers\User\UserDashboardController::class, 'updateLocation'])->name('location.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/seed-db-now', function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    return 'Trial accounts generated! You can now log in with the trial accounts.';
});

Route::get('/migrate-db-now', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return 'Database migrated successfully! You can now return to the dashboard.';
});
 
