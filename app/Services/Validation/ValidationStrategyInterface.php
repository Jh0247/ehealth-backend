<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;

/**
 * Interface ValidationStrategyInterface
 *
 * @package App\Services\Validation
 */
interface ValidationStrategyInterface
{
    /**
     * Validate the given request.
     *
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array;
}
