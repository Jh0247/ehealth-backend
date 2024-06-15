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

            // Insert into locations table
            if ($request->has('locations') && is_array($request->locations)) {
                foreach ($request->locations as $locationData) {
                    $organization->locations()->create([
                        'address' => $locationData['address'],
                        'latitude' => $locationData['latitude'],
                        'longitude' => $locationData['longitude'],
                    ]);
                }
            }

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

    // Display organization details with locations
    public function findOrganizationDetails($id)
    {
        $organization = Organization::with('locations')->find($id);

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
}
