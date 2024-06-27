<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UpdateBlogpostValidationStrategy
 *
 * @package App\Services\Validation
 */
class UpdateBlogpostValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the fields for updating a blogpost.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'banner' => 'nullable|image',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ['errors' => implode(' ', $errors)];
        }

        return ['errors' => null];
    }
}
