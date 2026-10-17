<?php

use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\AttendanceController;
use App\Http\Controllers\Student\FaceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// ---------- Autentikasi ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showStudentLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'studentLogin'])->middleware('throttle:10,1')->name('login.attempt');

    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:10,1')->name('admin.login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Admin ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('students', StudentController::class)->except('show');
    Route::delete('students/{student}/face', [StudentController::class, 'resetFace'])->name('students.face.reset');

    Route::get('attendances', [AttendanceReportController::class, 'index'])->name('attendances.index');
    Route::get('attendances/export', [AttendanceReportController::class, 'export'])->name('attendances.export');
    Route::get('attendances/{attendance}/photo/{type?}', [AttendanceReportController::class, 'photo'])
        ->whereIn('type', ['in', 'out'])->name('attendances.photo');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

// ---------- Mahasiswa ----------
Route::middleware(['auth', 'role:student'])->name('student.')->group(function () {
    Route::get('/absensi', [AttendanceController::class, 'create'])->name('attendance');
    Route::post('/absensi', [AttendanceController::class, 'store'])->middleware('throttle:20,1')->name('attendance.store');
    Route::post('/absensi/pulang', [AttendanceController::class, 'checkOut'])->middleware('throttle:20,1')->name('attendance.checkout');
    Route::get('/riwayat', [AttendanceController::class, 'history'])->name('history');

    Route::get('/wajah', [FaceController::class, 'edit'])->name('face.edit');
    Route::post('/wajah', [FaceController::class, 'store'])->name('face.store');
});
