<?php

namespace App\Services\Validation;

use Illuminate\Http\Request;

/**
 * Class ValidatorContext
 *
 * @package App\Services\Validation
 */
class ValidatorContext
{
    /**
     * @var array
     */
    private $strategies = [];

    /**
     * Add a validation strategy to the context.
     *
     * @param ValidationStrategyInterface $strategy
     */
    public function addStrategy(ValidationStrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * Validate the request using the added strategies.
     *
     * @param Request $request
     * @return array
     */
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
