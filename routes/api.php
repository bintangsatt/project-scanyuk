<?php

use App\Http\Controllers\ArProjectController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
| File ini otomatis di-load oleh Laravel dengan prefix /api
| dan TANPA VerifyCsrfToken middleware, sehingga fetch() dari JS bisa
| langsung kirim POST tanpa masalah CSRF redirect.
|--------------------------------------------------------------------------
*/

// Marker
Route::get('/markers',         [MarkerController::class,     'index'])->name('api.markers.index');
Route::post('/markers',        [MarkerController::class,     'upload'])->name('api.markers.upload');
Route::get('/marker/{marker}', [MarkerController::class,     'status'])->name('api.markers.status');

// Templates
Route::get('/templates',             [TemplateController::class, 'apiIndex'])->name('api.templates.index');
Route::get('/templates/{template}',  [TemplateController::class, 'apiShow'])->name('api.templates.show');

// Blend conversion (async via queue)
Route::post('/blend-upload',              [ArProjectController::class, 'blendUpload'])->name('api.blend.upload');
Route::get('/blend-status/{project}',     [ArProjectController::class, 'blendStatus'])->name('api.blend.status');

// Project (read)
Route::get('/project/{project}', [ArProjectController::class, 'apiShow'])->name('api.project.show');