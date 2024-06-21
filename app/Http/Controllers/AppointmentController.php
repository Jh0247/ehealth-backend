<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{

    public function getUserAppointments(Request $request)
    {
        $user = $request->user();

        if ($user->user_role === 'doctor') {
            $appointments = $user->appointmentsAsDoctor()->orderBy('appointment_datetime', 'desc')->get();
        } else {
            $appointments = $user->appointmentsAsUser()->orderBy('appointment_datetime', 'desc')->get();
        }

        return response()->json($appointments);
    }

    public function getAppointmentDetails($id)
    {
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

    public function bookAppointment(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
            'appointment_datetime' => 'required|date',
            'type' => 'required|string',
            'purpose' => 'nullable|string',
        ]);

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

    public function deleteAppointment($id)
    {
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

    public function getPatientsByDoctorAppointments(Request $request)
    {
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

    public function getAppointmentsByOrganization(Request $request, $organizationId)
    {
        $perPage = $request->input('per_page', 10);
        $appointments = Appointment::where('organization_id', $organizationId)
            ->orderBy('appointment_datetime', 'desc')
            ->paginate($perPage);

        return response()->json($appointments);
    }

    public function updateAppointmentWithPrescriptions(Request $request, $id)
    {
        $request->validate([
            'duration' => 'nullable|integer',
            'note' => 'nullable|string',
            'prescriptions' => 'required|array|min:1',
            'prescriptions.*.medication_id' => 'required|exists:medications,id',
            'prescriptions.*.dosage' => 'required|string',
            'prescriptions.*.frequency' => 'required|string',
            'prescriptions.*.duration' => 'required|integer',
        ]);

        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        if ($appointment->user_id !== Auth::id() && $appointment->doctor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update appointment details
        if ($request->has('duration')) {
            $appointment->duration = $request->duration;
        }
        if ($request->has('note')) {
            $appointment->note = $request->note;
        }

        // Handle prescriptions
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

        // Update the appointment status to completed
        $appointment->status = 'completed';
        $appointment->save();

        return response()->json([
            'message' => 'Appointment and prescriptions updated successfully',
            'appointment' => $appointment,
            'prescriptions' => $appointment->prescriptions
        ]);
    }

    public function updateAppointmentStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

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

    public function adminBookAppointment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
            'appointment_datetime' => 'required|date',
            'type' => 'required|string',
            'purpose' => 'nullable|string',
        ]);

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
}
