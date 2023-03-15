<?php

namespace Bigin\Migrate\Facades;

use Illuminate\Support\Facades\Facade;

class Migrate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'migrate';
    }
}
