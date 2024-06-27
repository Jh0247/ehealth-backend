<?php

namespace App\Http\Controllers;

use App\Facades\UserFacade;
use App\Factories\StaffUserFactory;
use App\Services\Validation\ValidatorContext;
use App\Services\Validation\OrganizationCreationValidationStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;

class OrganizationController extends Controller
{
    protected $organizationCreationValidatorContext;
    protected $userFacade;

    public function __construct(UserFacade $userFacade)
    {
        $this->organizationCreationValidatorContext = new ValidatorContext();
        $this->organizationCreationValidatorContext->addStrategy(new OrganizationCreationValidationStrategy());
        $this->userFacade = $userFacade;
    }

    // company collaboration and create their admin account
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
                'message' => 'Collaboration request successfully created, please wait for 1 - 3 working 
                                days to validate the admin account.',
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

    // Display organization details
    public function findOrganizationDetails($id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        return response()->json($organization);
    }

    // Get all organizations, except for id 1
    public function getAllOrganizations()
    {
        $organizations = Organization::where('id', '!=', 1)->get();

        return response()->json($organizations);
    }

    public function getOrganizationStats($id)
    {
        // Find the organization
        $organization = Organization::find($id);
    
        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }
    
        // Number of staff in the organization
        $numStaffs = $organization->users()->count();
    
        // Number of appointments in the organization
        $numAppointments = $organization->appointments()->count();
    
        // Number of blog posts created by users in the organization
        $numBlogposts = $organization->users()->withCount('blogposts')->get()->sum('blogposts_count');
    
        // Appointments data grouped by date for the past month (Line Chart)
        $startDate = Carbon::now()->subMonth();
        $dailyAppointments = $organization->appointments()
            ->whereBetween('appointment_datetime', [$startDate, Carbon::now()])
            ->selectRaw('DATE(appointment_datetime) as date, COUNT(*) as total_appointments')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    
        // Blogposts by users (Bar Chart)
        $blogpostsByUser = $organization->users()->withCount('blogposts')
            ->orderBy('blogposts_count', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'user_name' => $user->name,
                    'total_blogposts' => $user->blogposts_count,
                ];
            });
    
        // Staff distribution by roles (Pie Chart)
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
    
        // Return the statistics
        return response()->json([
            'num_staffs' => $numStaffs,
            'num_appointments' => $numAppointments,
            'num_blogposts' => $numBlogposts,
            'daily_appointments' => $dailyAppointments,
            'blogposts_by_user' => $blogpostsByUser,
            'staff_by_role' => $staffByRole
        ]);
    }

    public function adminViewAllOrganizations()
    {
        $organizations = Organization::where('id', '!=', 1)->get();

        $organizationsDetails = $organizations->map(function ($organization) {
            // Get the first admin
            $firstAdmin = User::where('organization_id', $organization->id)
                ->where('user_role', 'admin')
                ->orderBy('created_at', 'asc')
                ->first();

            // Number of staff in the organization
            $numStaffs = $organization->users()->count();

            // Number of appointments in the organization
            $numAppointments = $organization->appointments()->count();

            // Number of blog posts created by users in the organization
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
