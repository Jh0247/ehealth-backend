<?php

namespace App\Http\Controllers;

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

    public function __construct()
    {
        $this->organizationCreationValidatorContext = new ValidatorContext();
        $this->organizationCreationValidatorContext->addStrategy(new OrganizationCreationValidationStrategy());
    }

    // company collaboration and create their admin account
    public function createCollaborationRequest(Request $request)
    {
        $validationResult = $this->organizationCreationValidatorContext->validate($request);

        if ($validationResult['errors']) {
            return response()->json($validationResult['errors']->toJson(), 400);
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
                'password' => Hash::make($request->password),
                'user_role' => 'admin',
                'status' => 'pending'
            ];

            // Use AdminUserFactory to create the admin user
            $staffFactory = new StaffUserFactory();
            $user = $staffFactory->createUser($adminData);

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
}
