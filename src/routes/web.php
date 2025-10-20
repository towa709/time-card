<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceCorrectionController as AdminCorrection;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
  Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
  Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
  Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.break_in');
  Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.break_out');
  Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak'])->name('attendance.break.start');
  Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak'])->name('attendance.break.end');

  Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');
  Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
  Route::put('/attendance/detail/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

  Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'index'])->name('corrections.index');
  Route::get('/stamp_correction_request/{id}', [AttendanceCorrectionController::class, 'show'])->name('corrections.show');
  Route::post('/stamp_correction_request/store', [AttendanceCorrectionController::class, 'store'])->name('corrections.store');
});

Route::get('/email/verify', function () {
  $user = auth()->user();
  if (!$user) return redirect()->route('login');

  $verificationUrl = URL::temporarySignedRoute(
    'verification.verify',
    now()->addMinutes(60),
    ['id' => $user->id, 'hash' => sha1($user->email)]
  );

  return view('auth.verify-email', ['verificationUrl' => $verificationUrl]);
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
  $request->fulfill();
  return redirect()->route('attendance.create')->with('verified', true);
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
  $request->user()->sendEmailVerificationNotification();
  return back()->with('message', '確認メールを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::prefix('admin')->name('admin.')->group(function () {
  Route::get('/login', [AuthenticatedSessionController::class, 'create'])->middleware(['guest:admin'])->name('login');
  Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware(['guest:admin']);
  Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware(['auth:admin'])->name('logout');

  Route::middleware('auth:admin')->group(function () {
    Route::get('attendances/list', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/{id}', [AdminAttendanceController::class, 'show'])->name('attendances.show');
    Route::put('attendances/{id}', [AdminAttendanceController::class, 'update'])->name('attendances.update');

    Route::get('stamp_correction_request/list', [AdminCorrection::class, 'index'])->name('corrections.index');
    Route::get('stamp_correction_request/{id}', [AdminCorrection::class, 'show'])->name('corrections.show');
    Route::post('stamp_correction_request/approve/{attendance_correction_request_id}', [AdminCorrection::class, 'approve'])->name('corrections.approve');

    Route::get('staff/list', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('attendances/staff/{id}', [AdminAttendanceController::class, 'userAttendances'])->name('attendances.user');
    Route::get('attendances/staff/{id}/export-csv', [AdminAttendanceController::class, 'exportCsv'])->name('attendances.exportCsv');
  });
});
