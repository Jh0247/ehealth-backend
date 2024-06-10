<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;

interface ValidationStrategyInterface
{
    public function validate(Request $request): array;
}
