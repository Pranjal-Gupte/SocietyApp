<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\VisitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Protected routes (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Notices
    Route::resource('notices', NoticeController::class);
    
    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Complaints - Full CRUD
    Route::resource('complaints', ComplaintController::class);
    Route::post('complaints/{complaint}/update-status', [ComplaintController::class, 'updateStatus'])->name('complaints.update-status');
    Route::post('complaints/{complaint}/add-update', [ComplaintController::class, 'addUpdate'])->name('complaints.add-update');
    
    Route::get('/payments', function() {
        return view('coming-soon', ['feature' => 'Payments']);
    })->name('payments.index');
    
    Route::get('/bookings', function() {
        return view('coming-soon', ['feature' => 'Facility Booking']);
    })->name('bookings.index');
    
    // Member Directory (for all users - public facing)
    Route::get('/directory', function() {
        return view('directory.index');
    })->name('directory.index');

    // Member Management (admin/chairman only)
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('members.toggle-status');
    
    Route::get('/finance', function() {
        return view('coming-soon', ['feature' => 'Finance Management']);
    })->name('finance.index');
    
    Route::get('/documents', function() {
        return view('coming-soon', ['feature' => 'Documents']);
    })->name('documents.index');
    
    Route::get('/settings', function() {
        return view('coming-soon', ['feature' => 'Settings']);
    })->name('settings.index');

    Route::resource('visitors', VisitorController::class);
    Route::post('visitors/{visitor}/check-in', [VisitorController::class, 'checkIn'])->name('visitors.check-in');
    Route::post('visitors/{visitor}/check-out', [VisitorController::class, 'checkOut'])->name('visitors.check-out');
    Route::post('visitors/{visitor}/cancel', [VisitorController::class, 'cancel'])->name('visitors.cancel');
    Route::get('security/visitors', [VisitorController::class, 'security'])->name('visitors.security');
    Route::get('visitors/walkin/create', [VisitorController::class, 'walkinCreate'])->name('visitors.walkin.create');
    Route::post('visitors/walkin/store', [VisitorController::class, 'walkinStore'])->name('visitors.walkin.store');
    Route::post('visitors/{visitor}/approve', [VisitorController::class, 'approve'])->name('visitors.approve');
    Route::post('visitors/{visitor}/reject', [VisitorController::class, 'reject'])->name('visitors.reject');
});