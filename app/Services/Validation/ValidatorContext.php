<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;

class ValidatorContext
{
    private $strategies = [];

    public function addStrategy(ValidationStrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    public function validate(Request $request): array
    {
        foreach ($this->strategies as $strategy) {
            $result = $strategy->validate($request);
            if ($result['errors']) {
                return $result;
            }
        }

        return ['errors' => null];
    }
}
