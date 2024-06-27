<?php

namespace App\Http\Controllers;

use App\Facades\HealthRecordFacade;
use App\Models\Blogpost;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;

/**
 * CollaborationRequestController handles operations related to collaboration requests.
 */
class CollaborationRequestController extends Controller
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var HealthRecordFacade
     */
    protected $healthRecordFacade;

    /**
     * CollaborationRequestController constructor.
     *
     * @param UserRepositoryInterface $userRepository
     * @param HealthRecordFacade $healthRecordFacade
     */
    public function __construct(UserRepositoryInterface $userRepository, HealthRecordFacade $healthRecordFacade)
    {
        $this->userRepository = $userRepository;
        $this->healthRecordFacade = $healthRecordFacade;
    }

    /**
     * Approve a collaboration request for a specific user.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Decline a collaboration request for a specific user.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Stop collaboration for a specific organization.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopCollaboration(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id'
        ]);

        $organizationId = $request->input('organization_id');

        // Update all users' status in the organization to 'terminated'
        $users = User::where('organization_id', $organizationId)->get();
        foreach ($users as $user) {
            $user->status = 'terminated';
            $user->save();

            // Update all blogposts' status of the user to 'terminated'
            Blogpost::where('user_id', $user->id)->update(['status' => 'terminated']);
        }

        return response()->json(['message' => 'Collaboration stopped successfully, all users and blogposts have been terminated']);
    }

    /**
     * Get all collaboration requests for organizations with pending users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCollaborationRequests()
    {
        $organizations = Organization::whereHas('users', function ($query) {
            $query->where('status', 'pending');
        })->with(['users' => function ($query) {
            $query->where('status', 'pending');
        }])->get();

        return response()->json($organizations);
    }

    /**
     * Re-collaborate an organization by activating the first admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recollaborate(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id'
        ]);

        $organizationId = $request->input('organization_id');

        // Find the first admin of the organization
        $firstAdmin = User::where('organization_id', $organizationId)
            ->where('user_role', 'admin')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$firstAdmin) {
            return response()->json(['error' => 'Admin not found for this organization'], 404);
        }

        // Update the first admin's status to 'active'
        $firstAdmin->status = 'active';
        $firstAdmin->save();

        return response()->json(['message' => 'Collaboration reinstated successfully, the first admin account is now active', 'admin' => $firstAdmin]);
    }
}