<?php

namespace App\Http\Controllers;

use App\Facades\HealthRecordFacade;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;

class CollaborationRequestController extends Controller
{
    protected $userRepository;

    protected $healthRecordFacade;

    protected $healthRecordRepository;
    protected $healthRecordBuilder;

    public function __construct(UserRepositoryInterface $userRepository, HealthRecordFacade $healthRecordFacade)
    {
        $this->userRepository = $userRepository;
        $this->healthRecordFacade = $healthRecordFacade;
    }

    public function approveRequest(Request $request, $userId)
    {
        $user = $this->userRepository->find($userId);

        if (!$user || $user->status !== 'pending') {
            return response()->json(['error' => 'Invalid request or user not found'], 404);
        }

        $user->status = 'active';
        $this->userRepository->update($userId, ['status' => 'active']);

        // Facade create health record
        $this->healthRecordFacade->createHealthRecordForUser($user->id);

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
