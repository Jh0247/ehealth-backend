<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CollaborationRequestController;
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
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user-register', [RegistrationController::class, 'registerUser']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/collaboration-request', [OrganizationController::class, 'createCollaborationRequest']);

Route::middleware(['auth:sanctum', 'auth.user'])->group(function () {
    Route::get('/user-health-record', [HealthRecordController::class, 'getUserHealthRecord']);
    Route::get('/user-appointments', [AppointmentController::class, 'getUserAppointments']);
    Route::get('/user-medications', [MedicationController::class, 'getUserMedications']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
});

Route::middleware(['auth:sanctum', 'auth.admin'])->group(function () {
    Route::post('/admin-register', [RegistrationController::class, 'registerAdmin']);
    Route::post('/collaboration-request/approve/{userId}', [CollaborationRequestController::class, 'approveRequest']);
    Route::post('/collaboration-request/decline/{userId}', [CollaborationRequestController::class, 'declineRequest']);
});

Route::middleware(['auth:sanctum', 'auth.organization-admin'])->group(function () {
    Route::post('/staff-register', [RegistrationController::class, 'registerStaff']);
});