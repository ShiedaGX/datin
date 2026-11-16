<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
| Nanti taroh Api route di sini, misal: Route::get('/sektor', [SektorController::class, 'index']);
| Test API nanti ditaroh disini juga
| Test API bisa diakses di: http://localhost:8000/api/sektor
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
