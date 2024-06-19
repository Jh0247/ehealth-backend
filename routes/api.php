<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogpostController;
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

// Route for authenticated users
Route::middleware(['auth:sanctum', 'auth.user'])->group(function () {
    Route::get('/user-health-record', [HealthRecordController::class, 'getUserHealthRecord']);
    Route::get('/user-health-record/{userId}', [HealthRecordController::class, 'getSpecificUserHealthRecord']);
    Route::get('/user-appointments', [AppointmentController::class, 'getUserAppointments']);
    Route::get('/user-medications', [MedicationController::class, 'getUserMedications']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::put('/user/{id}/status', [UserController::class, 'updateUserStatus']);
    Route::get('/organization/{id}', [OrganizationController::class, 'findOrganizationDetails']);
    Route::get('/organization-list', [OrganizationController::class, 'getAllOrganizations']);
    Route::get('/organization/{organizationId}/users/{role}', [UserController::class, 'getUsersByRoleAndOrganization']);
    Route::get('/organization/{organizationId}/users', [UserController::class, 'getUsersByOrganization']);
    Route::post('/book-appointment', [AppointmentController::class, 'bookAppointment']);
    Route::get('/appointment/{id}', [AppointmentController::class, 'getAppointmentDetails']);
    Route::delete('/appointment/{id}', [AppointmentController::class, 'deleteAppointment']);
    Route::get('/patients-by-appointments', [AppointmentController::class, 'getPatientsByDoctorAppointments']);
    Route::get('/organization-stats/{id}', [OrganizationController::class, 'getOrganizationStats']);
    Route::get('/organization/{organizationId}/appointments', [AppointmentController::class, 'getAppointmentsByOrganization']);

    // Route for All Blog
    Route::post('/blogposts', [BlogpostController::class, 'createBlogpost']);
    Route::get('/blogposts', [BlogpostController::class, 'getAllBlogposts']);
    Route::put('/blogposts/{id}/status', [BlogpostController::class, 'updateBlogpostStatus']);
    Route::delete('/blogposts/{id}', [BlogpostController::class, 'deleteBlogpost']);
    Route::get('/blogposts/search/{name}', [BlogpostController::class, 'searchBlogpostByName']);
    Route::get('/blogposts/{id}', [BlogpostController::class, 'getSpecificBlogpost']);
    Route::post('/blogposts/{id}', [BlogpostController::class, 'updateBlogpost']);
    Route::get('/blogposts/status/{status}', [BlogpostController::class, 'getBlogpostsByStatus']);
    Route::get('/user/blogposts', [BlogpostController::class, 'getUserBlogposts']);
});
// Route for e-health admin
Route::middleware(['auth:sanctum', 'auth.admin'])->group(function () {
    Route::post('/admin-register', [RegistrationController::class, 'registerAdmin']);
    Route::post('/collaboration-request/approve/{userId}', [CollaborationRequestController::class, 'approveRequest']);
    Route::post('/collaboration-request/decline/{userId}', [CollaborationRequestController::class, 'declineRequest']);
});
// Route for collaborated organization admin
Route::middleware(['auth:sanctum', 'auth.organization-admin'])->group(function () {
    Route::post('/staff-register', [RegistrationController::class, 'registerStaff']);
});