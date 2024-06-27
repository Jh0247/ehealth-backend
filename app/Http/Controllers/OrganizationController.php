<?php

namespace App\Http\Controllers;

use App\Facades\UserFacade;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\OrganizationCreationValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;

/**
 * OrganizationController handles all operations related to organizations.
 */
class OrganizationController extends Controller
{
    /**
     * @var ValidatorContext
     */
    protected $organizationCreationValidatorContext;

    /**
     * @var UserFacade
     */
    protected $userFacade;

    /**
     * OrganizationController constructor.
     *
     * @param UserFacade $userFacade
     */
    public function __construct(UserFacade $userFacade)
    {
        $this->organizationCreationValidatorContext = new ValidatorContext();
        $this->organizationCreationValidatorContext->addStrategy(new OrganizationCreationValidationStrategy());
        $this->userFacade = $userFacade;
    }

    /**
     * Create a collaboration request and create an admin account for the organization.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCollaborationRequest(Request $request)
    {
        $validationResult = $this->organizationCreationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json(['error' => $validationResult['errors']], 400);
        }

        DB::beginTransaction();
        try {
            // Insert into organizations table
            $organization = Organization::create([
                'name' => $request->organization_name,
                'code' => $request->organization_code,
                'type' => $request->organization_type,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Prepare admin data
            $adminData = [
                'organization_id' => $organization->id,
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'contact' => $request->admin_contact,
                'icno' => $request->admin_icno,
                'password' => $request->password,
                'user_role' => 'admin',
                'status' => 'pending'
            ];

            // Use UserFacade to create the admin user
            $user = $this->userFacade->createUser($adminData, 'admin');

            DB::commit();

            return response()->json([
                'message' => 'Collaboration request successfully created, please wait for 1 - 3 working days to validate the admin account.',
                'organization' => $organization,
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create collaboration request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display organization details.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function findOrganizationDetails($id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        return response()->json($organization);
    }

    /**
     * Get all organizations except for id 1.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllOrganizations()
    {
        $organizations = Organization::where('id', '!=', 1)->get();

        return response()->json($organizations);
    }

    /**
     * Get organization statistics by organization ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizationStats($id)
    {
        $organization = Organization::find($id);
    
        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }
    
        $numStaffs = $organization->users()->count();
        $numAppointments = $organization->appointments()->count();
        $numBlogposts = $organization->users()->withCount('blogposts')->get()->sum('blogposts_count');
    
        $startDate = Carbon::now()->subMonth();
        $dailyAppointments = $organization->appointments()
            ->whereBetween('appointment_datetime', [$startDate, Carbon::now()])
            ->selectRaw('DATE(appointment_datetime) as date, COUNT(*) as total_appointments')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    
        $blogpostsByUser = $organization->users()->withCount('blogposts')
            ->orderBy('blogposts_count', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'user_name' => $user->name,
                    'total_blogposts' => $user->blogposts_count,
                ];
            });
    
        $staffByRole = $organization->users()
            ->selectRaw('user_role, COUNT(*) as total')
            ->groupBy('user_role')
            ->get()
            ->map(function ($role) {
                return [
                    'role' => $role->user_role,
                    'total' => $role->total,
                ];
            });
    
        return response()->json([
            'num_staffs' => $numStaffs,
            'num_appointments' => $numAppointments,
            'num_blogposts' => $numBlogposts,
            'daily_appointments' => $dailyAppointments,
            'blogposts_by_user' => $blogpostsByUser,
            'staff_by_role' => $staffByRole
        ]);
    }

    /**
     * Admin view for all organizations except for id 1.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminViewAllOrganizations()
    {
        $organizations = Organization::where('id', '!=', 1)->get();

        $organizationsDetails = $organizations->map(function ($organization) {
            $firstAdmin = User::where('organization_id', $organization->id)
                ->where('user_role', 'admin')
                ->orderBy('created_at', 'asc')
                ->first();

            $numStaffs = $organization->users()->count();
            $numAppointments = $organization->appointments()->count();
            $numBlogposts = $organization->users()->withCount('blogposts')->get()->sum('blogposts_count');

            return [
                'organization' => $organization,
                'first_admin' => $firstAdmin,
                'stats' => [
                    'num_staffs' => $numStaffs,
                    'num_appointments' => $numAppointments,
                    'num_blogposts' => $numBlogposts
                ]
            ];
        });

        return response()->json($organizationsDetails);
    }
}
