<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
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
            'doctor',
            'organization',
            'organization.locations',
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
}
