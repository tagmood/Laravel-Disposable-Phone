<?php

namespace Tagmood\LaravelDisposablePhone\Facades;

use Illuminate\Support\Facades\Facade;

class DisposableNumbers extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'disposable_email.domains';
    }
}