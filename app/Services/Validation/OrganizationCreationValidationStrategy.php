<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationCreationValidationStrategy implements ValidationStrategyInterface
{
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'organization_code' => 'required|string|max:15|unique:organizations,code',
            'organization_type' => 'required|string|max:5',
            'admin_name' => 'required|string|between:2,100',
            'admin_email' => 'required|string|email|max:100|unique:users,email',
            'admin_contact' => 'required|string|between:10,15',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        return ['errors' => null];
    }
}
