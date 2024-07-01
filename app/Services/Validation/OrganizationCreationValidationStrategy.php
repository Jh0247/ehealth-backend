<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class OrganizationCreationValidationStrategy
 *
 * @package App\Services\Validation
 */
class OrganizationCreationValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the organization creation fields in the request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'organization_code' => 'required|string|max:15|unique:organizations,code',
            'organization_type' => 'required|string|max:5',
            'admin_name' => 'required|string|between:2,100',
            'admin_email' => 'required|string|email|max:100|unique:users,email',
            'admin_contact' => 'required|string|between:10,15',
            'admin_icno' => 'required|string|between:12,14|unique:users,icno',
            'password' => 'required|string|confirmed|min:8',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
