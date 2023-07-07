<?php

namespace Vuetik\VuetikLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vuetik\VuetikLaravel\VuetikLaravel
 */
class VuetikLaravel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Vuetik\VuetikLaravel\VuetikLaravel::class;
    }
}
