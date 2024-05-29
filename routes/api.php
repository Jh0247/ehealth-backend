<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganizationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::get('/csrf-token', function () {
//     return response()->json(['csrfToken' => csrf_token()]);
// });
Route::post('/user-register', [AuthController::class, 'userRegister']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/collaboration-request', [OrganizationController::class, 'createCollaborationRequest']);
