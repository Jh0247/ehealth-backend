<?php

namespace App\Http\Controllers;

use App\Facades\HealthRecordFacade;
use App\Facades\UserFacade;
use App\Models\Blogpost;
use App\Models\Organization;
use Illuminate\Http\Request;

/**
 * CollaborationRequestController handles all operations related to collaboration requests.
 */
class CollaborationRequestController extends Controller
{
    /**
     * @var HealthRecordFacade
     */
    protected $healthRecordFacade;

    /**
     * @var UserFacade
     */
    protected $userFacade;

    /**
     * CollaborationRequestController constructor.
     *
     * @param HealthRecordFacade $healthRecordFacade
     * @param UserFacade $userFacade
     */
    public function __construct(HealthRecordFacade $healthRecordFacade, UserFacade $userFacade)
    {
        $this->healthRecordFacade = $healthRecordFacade;
        $this->userFacade = $userFacade;
    }

    /**
     * Approve a collaboration request.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveRequest(Request $request, $userId)
    {
        $user = $this->userFacade->findUser($userId);

        if (!$user || $user->status !== 'pending') {
            return response()->json(['error' => 'Invalid request or user not found'], 404);
        }

        $this->userFacade->updateUser($userId, ['status' => 'active']);

        // Facade create health record
        $this->healthRecordFacade->createHealthRecordForUser($user->id);

        return response()->json(['message' => 'Collaboration request approved successfully', 'user' => $user], 200);
    }

    /**
     * Decline a collaboration request.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function declineRequest(Request $request, $userId)
    {
        $user = $this->userFacade->findUser($userId);

        if (!$user || $user->status !== 'pending') {
            return response()->json(['error' => 'Invalid request or user not found'], 404);
        }

        $this->userFacade->updateUser($userId, ['status' => 'terminated']);

        return response()->json(['message' => 'Collaboration request declined successfully', 'user' => $user], 200);
    }

    /**
     * Stop collaboration for an organization.
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
        $users = $this->userFacade->findUsersByOrganization($organizationId);
        foreach ($users as $user) {
            $this->userFacade->updateUser($user->id, ['status' => 'terminated']);

            // Update all blogposts' status of the user to 'terminated'
            Blogpost::where('user_id', $user->id)->update(['status' => 'terminated']);
        }

        return response()->json(['message' => 'Collaboration stopped successfully, all users and blogposts have been terminated']);
    }

    /**
     * Get all pending collaboration requests.
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
     * Re-collaborate for an organization by activating the first admin.
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
        $firstAdmin = $this->userFacade->findFirstAdmin($organizationId);

        if (!$firstAdmin) {
            return response()->json(['error' => 'Admin not found for this organization'], 404);
        }

        // Update the first admin's status to 'active'
        $this->userFacade->updateUser($firstAdmin->id, ['status' => 'active']);

        return response()->json(['message' => 'Collaboration reinstated successfully, the first admin account is now active', 'admin' => $firstAdmin]);
    }
}
