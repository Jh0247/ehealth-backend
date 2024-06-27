<?php

namespace App\Http\Controllers;

use App\Facades\HealthRecordFacade;
use App\Models\User;
use App\Repositories\HealthRecord\HealthRecordRepositoryInterface;
use Illuminate\Http\Request;

/**
 * HealthRecordController handles all operations related to health records.
 */
class HealthRecordController extends Controller
{
    /**
     * @var HealthRecordRepositoryInterface
     */
    protected $healthRecordRepository;

    /**
     * @var HealthRecordFacade
     */
    protected $healthRecordFacade;

    /**
     * HealthRecordController constructor.
     *
     * @param HealthRecordRepositoryInterface $healthRecordRepository
     * @param HealthRecordFacade $healthRecordFacade
     */
    public function __construct(HealthRecordRepositoryInterface $healthRecordRepository, HealthRecordFacade $healthRecordFacade)
    {
        $this->healthRecordRepository = $healthRecordRepository;
        $this->healthRecordFacade = $healthRecordFacade;
    }

    /**
     * Get the health record of the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserHealthRecord(Request $request)
    {
        $user = $request->user;

        if (!$user) {
            return response()->json(['error' => 'User not found in request.'], 401);
        }

        $user = User::find($user->id)->load('healthRecord');

        $response = [
            'profile_img' => $user->profile_img,
            'name' => $user->name,
            'icno' => $user->icno,
            'email' => $user->email,
            'contact' => $user->contact,
            'health_record' => $user->healthRecord
        ];
    
        return response()->json($response);
    }

    /**
     * Get the health record of a specific user by user ID.
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecificUserHealthRecord($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $user->load('healthRecord');

        if (!$user->healthRecord) {
            return response()->json(['error' => 'Health record not found for the specified user.'], 404);
        }

        $response = [
            'profile_img' => $user->profile_img,
            'name' => $user->name,
            'icno' => $user->icno,
            'email' => $user->email,
            'contact' => $user->contact,
            'health_record' => $user->healthRecord
        ];
    
        return response()->json($response);
    }

    /**
     * Update the health record of a specific user by health record ID.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateHealthRecord(Request $request, $id)
    {
        $request->validate([
            'health_condition' => 'nullable|string|in:Healthy,Good,Fair,Poor',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergic' => 'nullable|array',
            'diseases' => 'nullable|string',
        ]);
    
        $allergic = $request->allergic ? json_encode($request->allergic) : null;
    
        $updatedRecord = $this->healthRecordFacade->updateHealthRecord(
            $id,
            $request->health_condition,
            $request->blood_type,
            $allergic,
            $request->diseases
        );
    
        if (!$updatedRecord) {
            return response()->json(['error' => 'Health record not found or update failed'], 404);
        }

        $user = User::find($updatedRecord->user_id);
    
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }
    
        $user->load('healthRecord');
    
        if (!$user->healthRecord) {
            return response()->json(['error' => 'Health record not found for the specified user.'], 404);
        }
    
        $response = [
            'profile_img' => $user->profile_img,
            'name' => $user->name,
            'icno' => $user->icno,
            'email' => $user->email,
            'contact' => $user->contact,
            'health_record' => $user->healthRecord
        ];

        return response()->json($response);
    }
}