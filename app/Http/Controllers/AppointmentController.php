<?php
/**
 * AppointmentController handles all operations related to appointments.
 */

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Prescription;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\BookAppointmentValidationStrategy;
use App\Services\Validation\UpdateAppointmentWithPrescriptionsValidationStrategy;
use App\Services\Validation\UpdateAppointmentStatusValidationStrategy;
use App\Services\Validation\AdminBookAppointmentValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller {

    /**
     * @var ValidatorContext
     */
    protected $validatorContext;

    public function __construct() {
        $this->validatorContext = new ValidatorContext();
    }

    /**
     * Get the list of appointments for the authenticated user.
     */
    public function getUserAppointments(Request $request) {
        $user = $request->user();

        if ($user->user_role === 'doctor') {
            $appointments = $user->appointmentsAsDoctor()->orderBy('appointment_datetime', 'desc')->get();
        } else {
            $appointments = $user->appointmentsAsUser()->orderBy('appointment_datetime', 'desc')->get();
        }

        return response()->json($appointments);
    }

    /**
     * Get the details of a specific appointment by its ID.
     */
    public function getAppointmentDetails($id) {
        $appointment = Appointment::with([
            'user',
            'user.healthRecord',
            'doctor',
            'organization',
            'prescriptions',
            'prescriptions.medication',
            'prescriptions.medication.purchaseRecords'
        ])->find($id);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        return response()->json($appointment);
    }

    /**
     * Book a new appointment.
     */
    public function bookAppointment(Request $request) {
        $this->validatorContext->addStrategy(new BookAppointmentValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'doctor_id' => $request->doctor_id,
            'organization_id' => $request->organization_id,
            'appointment_datetime' => $request->appointment_datetime,
            'type' => $request->type,
            'purpose' => $request->purpose,
            'duration' => null,
            'note' => null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment
        ], 201);
    }

    /**
     * Delete a specific appointment by its ID.
     */
    public function deleteAppointment($id) {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        if ($appointment->user_id !== Auth::id() && $appointment->doctor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully'], 200);
    }

    /**
     * Get a list of patients by the doctor's appointments.
     */
    public function getPatientsByDoctorAppointments(Request $request) {
        $user = $request->user();

        if ($user->user_role !== 'doctor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $appointments = Appointment::where('doctor_id', $user->id)
            ->with('user')
            ->get();

        $patients = $appointments->pluck('user')->unique('id');

        return response()->json($patients->values());
    }

    /**
     * Get a list of appointments by organization with pagination.
     */
    public function getAppointmentsByOrganization(Request $request, $organizationId) {
        $perPage = $request->input('per_page', 500);
        $appointments = Appointment::with('user')
            ->where('organization_id', $organizationId)
            ->orderBy('appointment_datetime', 'desc')
            ->paginate($perPage);

        return response()->json($appointments);
    }

    /**
     * Update an appointment and its prescriptions.
     */
    public function updateAppointmentWithPrescriptions(Request $request, $id) {
        $this->validatorContext->addStrategy(new UpdateAppointmentWithPrescriptionsValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        if ($appointment->user_id !== Auth::id() && $appointment->doctor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->has('duration')) {
            $appointment->duration = $request->duration;
        }
        if ($request->has('note')) {
            $appointment->note = $request->note;
        }

        foreach ($request->prescriptions as $prescriptionData) {
            $prescription = new Prescription([
                'appointment_id' => $appointment->id,
                'medication_id' => $prescriptionData['medication_id'],
                'dosage' => $prescriptionData['dosage'],
                'frequency' => $prescriptionData['frequency'],
                'duration' => $prescriptionData['duration'],
                'prescription_date' => now(),
                'start_date' => now(),
                'end_date' => now()->addDays($prescriptionData['duration']),
            ]);
            $prescription->save();
        }

        $appointment->status = 'completed';
        $appointment->save();

        return response()->json([
            'message' => 'Appointment and prescriptions updated successfully',
            'appointment' => $appointment,
            'prescriptions' => $appointment->prescriptions
        ]);
    }

    /**
     * Update the status of an appointment.
     */
    public function updateAppointmentStatus(Request $request, $id) {
        $this->validatorContext->addStrategy(new UpdateAppointmentStatusValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        $appointment->status = $request->status;
        $appointment->save();

        return response()->json([
            'message' => 'Appointment status updated successfully',
            'appointment' => $appointment,
        ]);
    }

    /**
     * Book an appointment as an admin for a user.
     */
    public function adminBookAppointment(Request $request) {
        $this->validatorContext->addStrategy(new AdminBookAppointmentValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        $appointment = Appointment::create([
            'user_id' => $request->user_id,
            'doctor_id' => $request->doctor_id,
            'organization_id' => $request->organization_id,
            'appointment_datetime' => $request->appointment_datetime,
            'type' => $request->type,
            'purpose' => $request->purpose,
            'duration' => null,
            'note' => null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment
        ], 201);
    }

    /**
     * Get the list of prescriptions for a specific appointment.
     */
    public function getAppointmentPrescriptions($id) {
        $appointment = Appointment::with([
            'prescriptions',
            'prescriptions.medication'
        ])->find($id);
    
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }
    
        return response()->json([
            'appointment_id' => $appointment->id,
            'prescriptions' => $appointment->prescriptions->map(function($prescription) {
                return [
                    'id' => $prescription->id,
                    'medication' => [
                        'id' => $prescription->medication->id,
                        'name' => $prescription->medication->name,
                        'description' => $prescription->medication->description,
                        'ingredient' => $prescription->medication->ingredient,
                        'form' => $prescription->medication->form,
                        'usage' => $prescription->medication->usage,
                        'strength' => $prescription->medication->strength,
                        'manufacturer' => $prescription->medication->manufacturer,
                        'price' => $prescription->medication->price,
                    ],
                    'dosage' => $prescription->dosage,
                    'frequency' => $prescription->frequency,
                    'duration' => $prescription->duration,
                    'prescription_date' => $prescription->prescription_date,
                    'start_date' => $prescription->start_date,
                    'end_date' => $prescription->end_date,
                ];
            })
        ]);
    }
}
