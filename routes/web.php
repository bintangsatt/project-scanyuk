<?php

use App\Http\Controllers\ArProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes  (routes/web.php)
| Semua route di sini memakai session + CSRF.
| Route /api/* sudah dipindah ke routes/api.php (bebas CSRF).
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Wizard
Route::get('/create', [ArProjectController::class, 'create'])->name('ar.create');
Route::post('/store',  [ArProjectController::class, 'store'])->name('ar.store');

// Result & AR Viewer
Route::get('/result/{project}', [ArProjectController::class, 'result'])->name('ar.result');
Route::get('/ar/{project}',     [ArProjectController::class, 'view'])->name('ar.view');

// Templates (halaman web)
Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');