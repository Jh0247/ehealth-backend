<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicationController extends Controller
{
    public function getUserMedications(Request $request)
    {
        $user = $request->user();
        $user = User::find($user->id)->load('appointmentsAsUser.prescriptions.medication');
    
        $prescriptions = $user->appointmentsAsUser->flatMap(function ($appointment) {
            return $appointment->prescriptions->map(function ($prescription) {
                return [
                    'id' => $prescription->id,
                    'medication_id' => $prescription->medication->id,
                    'medication_name' => $prescription->medication->name,
                    'dosage' => $prescription->dosage,
                    'frequency' => $prescription->frequency,
                    'duration' => $prescription->duration,
                    'prescription_date' => $prescription->prescription_date,
                    'start_date' => $prescription->start_date,
                    'end_date' => $prescription->end_date,
                ];
            });
        });
        $sortedPrescriptions = $prescriptions->sortBy('start_date')->values();
        return response()->json($sortedPrescriptions);
    }

    public function getSpecificUserMedications($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }
        $user->load('appointmentsAsUser.prescriptions.medication');
        $prescriptions = $user->appointmentsAsUser->flatMap(function ($appointment) {
            return $appointment->prescriptions->map(function ($prescription) {
                return [
                    'id' => $prescription->id,
                    'medication_id' => $prescription->medication->id,
                    'medication_name' => $prescription->medication->name,
                    'dosage' => $prescription->dosage,
                    'frequency' => $prescription->frequency,
                    'duration' => $prescription->duration,
                    'prescription_date' => $prescription->prescription_date,
                    'start_date' => $prescription->start_date,
                    'end_date' => $prescription->end_date,
                ];
            });
        });
        if ($prescriptions->isEmpty()) {
            return response()->json(['error' => 'No Medication Record.'], 404);
        }
        $sortedPrescriptions = $prescriptions->sortBy('start_date')->values();
        return response()->json($sortedPrescriptions);
    }

    public function getAllMedications()
    {
        $medications = Medication::all();
        return response()->json($medications);
    }

    public function getMedicationDetails($id)
    {
        $medication = Medication::find($id);

        if (!$medication) {
            return response()->json(['error' => 'Medication not found.'], 404);
        }

        return response()->json($medication);
    }
}
