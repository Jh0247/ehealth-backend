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
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseRecordController;
use App\Http\Controllers\StatisticsController;
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
    // Route for Healthrecord
    Route::get('/user-health-record', [HealthRecordController::class, 'getUserHealthRecord']);
    Route::get('/user-health-record/{userId}', [HealthRecordController::class, 'getSpecificUserHealthRecord']);
    Route::put('/health-record/{id}', [HealthRecordController::class, 'updateHealthRecord']);

    // Route for User
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::put('/user/{id}/status', [UserController::class, 'updateUserStatus']);
    Route::get('/organization/{organizationId}/users/{role}', [UserController::class, 'getUsersByRoleAndOrganization']);
    Route::get('/organization/{organizationId}/users', [UserController::class, 'getUsersByOrganization']);
    Route::get('/search-user', [UserController::class, 'searchUserByIcno']);
    Route::put('/user/update-password', [UserController::class, 'updatePassword']);

    //Route for organization
    Route::get('/organization/{id}', [OrganizationController::class, 'findOrganizationDetails']);
    Route::get('/organization-list', [OrganizationController::class, 'getAllOrganizations']);
    Route::get('/organization-stats/{id}', [OrganizationController::class, 'getOrganizationStats']);

    // Route for Appointment
    Route::get('/user-appointments', [AppointmentController::class, 'getUserAppointments']);
    Route::post('/book-appointment', [AppointmentController::class, 'bookAppointment']);
    Route::get('/appointment/{id}', [AppointmentController::class, 'getAppointmentDetails']);
    Route::delete('/appointment/{id}', [AppointmentController::class, 'deleteAppointment']);
    Route::get('/patients-by-appointments', [AppointmentController::class, 'getPatientsByDoctorAppointments']);
    Route::get('/organization/{organizationId}/appointments', [AppointmentController::class, 'getAppointmentsByOrganization']);
    Route::post('/appointment/{id}', [AppointmentController::class, 'updateAppointmentWithPrescriptions']);
    Route::put('/appointment/{id}/status', [AppointmentController::class, 'updateAppointmentStatus']);
    Route::get('/appointment/{id}/prescriptions', [AppointmentController::class, 'getAppointmentPrescriptions']);

    // Route for Blog
    Route::post('/blogposts', [BlogpostController::class, 'createBlogpost']);
    Route::get('/blogposts', [BlogpostController::class, 'getAllBlogposts']);
    Route::put('/blogposts/{id}/status', [BlogpostController::class, 'updateBlogpostStatus']);
    Route::delete('/blogposts/{id}', [BlogpostController::class, 'deleteBlogpost']);
    Route::get('/blogposts/search/{name}', [BlogpostController::class, 'searchBlogpostByName']);
    Route::get('/blogposts/{id}', [BlogpostController::class, 'getSpecificBlogpost']);
    Route::post('/blogposts/{id}', [BlogpostController::class, 'updateBlogpost']);
    Route::get('/blogposts/status/{status}', [BlogpostController::class, 'getBlogpostsByStatus']);
    Route::get('/user/blogposts', [BlogpostController::class, 'getUserBlogposts']);

    // Route for Medication
    Route::get('/medications/{id}', [MedicationController::class, 'getMedicationDetails']);
    Route::get('/user-medications', [MedicationController::class, 'getUserMedications']);
    Route::get('/user-medications/{userId}', [MedicationController::class, 'getSpecificUserMedications']);
    Route::get('/medications', [MedicationController::class, 'getAllMedications']);

    // Route for Purchase
    Route::get('/organization/{organizationId}/purchases', [PurchaseController::class, 'getAllPurchasesByOrganization']);
    Route::post('/purchases', [PurchaseController::class, 'createPurchaseRecord']);
    Route::delete('/purchases/{id}', [PurchaseController::class, 'deletePurchaseRecord']);
    Route::get('/organization/{organizationId}/purchase-statistics', [PurchaseController::class, 'getPurchaseStatistics']);

    // Route for Purchase Record
    Route::get('/user-purchases', [PurchaseRecordController::class, 'getUserPurchaseRecords']);
});
// Route for e-health admin
Route::middleware(['auth:sanctum', 'auth.admin'])->group(function () {
    Route::post('/admin-register', [RegistrationController::class, 'registerAdmin']);
    Route::post('/collaboration-request/approve/{userId}', [CollaborationRequestController::class, 'approveRequest']);
    Route::post('/collaboration-request/decline/{userId}', [CollaborationRequestController::class, 'declineRequest']);
    Route::post('/stop-collaboration', [CollaborationRequestController::class, 'stopCollaboration']);
    Route::post('/medications', [MedicationController::class, 'createMedication']);
    Route::put('/medications/{id}', [MedicationController::class, 'updateMedication']);
    Route::get('/admin-view-all-organization', [OrganizationController::class, 'adminViewAllOrganizations']);
    Route::get('/admin/statistics/user-registrations', [StatisticsController::class, 'userRegistrations']);
    Route::get('/admin/statistics/blogpost-status', [StatisticsController::class, 'blogpostStatus']);
    Route::get('/admin/statistics/appointments-by-type', [StatisticsController::class, 'appointmentsByType']);
    Route::get('/admin/statistics/sales-over-time', [StatisticsController::class, 'salesOverTime']);
    Route::get('/admin/statistics/medications-sold', [StatisticsController::class, 'medicationsSold']);
    Route::get('/admin/statistics/organization-stats/{id}', [StatisticsController::class, 'organizationStats']);
    Route::get('/collaboration-requests', [CollaborationRequestController::class, 'getCollaborationRequests']);
    Route::post('/recollaborate', [CollaborationRequestController::class, 'recollaborate']);
});
// Route for collaborated organization admin
Route::middleware(['auth:sanctum', 'auth.organization-admin'])->group(function () {
    Route::post('/staff-register', [RegistrationController::class, 'registerStaff']);
    Route::post('/admin-book-appointment', [AppointmentController::class, 'adminBookAppointment']);
});