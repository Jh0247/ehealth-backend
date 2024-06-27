<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class CreateBlogpostValidationStrategy
 *
 * @package App\Services\Validation
 */
class CreateBlogpostValidationStrategy implements ValidationStrategyInterface
{
    /**
     * Validate the fields for creating a new blogpost.
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
