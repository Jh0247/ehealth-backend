<?php

namespace App\Http\Controllers;

use App\Builders\HealthRecordBuilder;
use App\Repositories\HealthRecord\HealthRecordRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;

class CollaborationRequestController extends Controller
{
    protected $userRepository;
    protected $healthRecordRepository;
    protected $healthRecordBuilder;

    public function __construct(UserRepositoryInterface $userRepository, HealthRecordRepositoryInterface $healthRecordRepository, HealthRecordBuilder $healthRecordBuilder)
    {
        $this->userRepository = $userRepository;
        $this->healthRecordRepository = $healthRecordRepository;
        $this->healthRecordBuilder = $healthRecordBuilder;
    }

    public function approveRequest(Request $request, $userId)
    {
        $user = $this->userRepository->find($userId);

        if (!$user || $user->status !== 'pending') {
            return response()->json(['error' => 'Invalid request or user not found'], 404);
        }

        $user->status = 'active';
        $this->userRepository->update($userId, ['status' => 'active']);

        $this->healthRecordRepository->create([
            'user_id' => $user->id,
            'health_condition' => null,
            'blood_type' => null,
            'allergic' => null,
            'diseases' => null,
        ]);

        $healthRecordData = $this->healthRecordBuilder
            ->setUserId($user->id)
            ->setHealthCondition(null)
            ->setBloodType(null)
            ->setAllergic(null)
            ->setDiseases(null)
            ->build();

        $this->healthRecordRepository->create($healthRecordData);

        return response()->json(['message' => 'Collaboration request approved successfully', 'user' => $user], 200);
    }

    public function declineRequest(Request $request, $userId)
    {
        $user = $this->userRepository->find($userId);

        if (!$user || $user->status !== 'pending') {
            return response()->json(['error' => 'Invalid request or user not found'], 404);
        }

        $user->status = 'terminated';
        $this->userRepository->update($userId, ['status' => 'terminated']);

        return response()->json(['message' => 'Collaboration request declined successfully', 'user' => $user], 200);
    }
}
