<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\admin\RequestController as AdminRequestController;
use App\Http\Controllers\admin\UserController as AdminUserController;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\RequestController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('/login', function (LoginRequest $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/attendance');
    }
    return back()->withErrors(['login' => 'ログイン情報が登録されていません'])->withInput();
});

Route::get('/admin/login', function () {
    return view('admin.login');
})->middleware('guest');

Route::post('/admin/login', function (LoginRequest $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        if (Auth::user()->role !== \App\Models\User::ROLE_ADMIN) {
            Auth::logout();
            return back()->withErrors(['login' => '管理者のみログイン可能です'])->withInput();
        }

        return redirect()->intended('/admin/attendances');
    }

    return back()->withErrors(['login' => 'ログイン情報が登録されていません'])->withInput();
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance', [AttendanceController::class, 'store']);

    Route::get('/attendance/list', [AttendanceController::class, 'list']);

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show']);

    Route::get('/stamp_correction_request/list', [RequestController::class, 'index']);

    Route::post('/requests', [RequestController::class, 'store']);
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/attendances', [AdminAttendanceController::class, 'index']);
    Route::get('/attendances/{id}', [AdminAttendanceController::class, 'show']);
    Route::put('/attendances/{id}', [AdminAttendanceController::class, 'update']);

    Route::get('/users', [AdminUserController::class, 'index']);

    Route::get('/users/{user}/attendances', [AdminAttendanceController::class, 'userAttendances']);

    Route::get('/users/{user}/attendances/export', [AdminAttendanceController::class, 'exportCsv']);

    Route::get('/requests', [AdminRequestController::class, 'index']);
    Route::get('/requests/{id}', [AdminRequestController::class, 'show']);

    Route::post('/requests/{id}/approve', [AdminRequestController::class, 'approve']);

});