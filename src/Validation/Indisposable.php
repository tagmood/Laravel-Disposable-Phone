<?php

namespace Tagmood\LaravelDisposablePhone\Validation;

use Tagmood\LaravelDisposablePhone\Facades\DisposableNumbers;

class Indisposable
{
    /**
     * Default error message.
     *
     * @var string
     */
    public static $errorMessage = 'Disposable phone addresses are not allowed.';

    /**
     * Validates whether an phone address does not originate from a disposable phone service.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        return DisposableNumbers::isNotDisposable($value);
    }
}
