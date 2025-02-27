<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\User;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\CreateMedicationValidationStrategy;
use App\Services\Validation\UpdateMedicationValidationStrategy;
use Illuminate\Http\Request;

/**
 * MedicationController handles all operations related to medications.
 */
class MedicationController extends Controller
{
    /**
     * @var ValidatorContext
     */
    protected $validatorContext;

    public function __construct()
    {
        $this->validatorContext = new ValidatorContext();
    }

    /**
     * Get the list of medications for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Get the list of medications for a specific user by user ID.
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Get all medications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllMedications()
    {
        $medications = Medication::all();
        return response()->json($medications);
    }

    /**
     * Get details of a specific medication by medication ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMedicationDetails($id)
    {
        $medication = Medication::find($id);

        if (!$medication) {
            return response()->json(['error' => 'Medication not found.'], 404);
        }

        return response()->json($medication);
    }

    /**
     * Create a new medication.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMedication(Request $request)
    {
        $this->validatorContext->addStrategy(new CreateMedicationValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 422);
        }

        $medication = Medication::create($request->all());

        return response()->json(['message' => 'Medication created successfully', 'medication' => $medication], 201);
    }

    /**
     * Update details of a specific medication by medication ID.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMedication(Request $request, $id)
    {
        $this->validatorContext->addStrategy(new UpdateMedicationValidationStrategy());
        $validationResult = $this->validatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 422);
        }

        $medication = Medication::find($id);

        if (!$medication) {
            return response()->json(['error' => 'Medication not found.'], 404);
        }

        $medication->update($request->all());

        return response()->json(['message' => 'Medication updated successfully', 'medication' => $medication]);
    }
}
