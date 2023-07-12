<?php

namespace Vuetik\VuetikLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vuetik\VuetikLaravel\VuetikLaravel
 *
 * @method static void routes()
 */
class VuetikLaravel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Vuetik\VuetikLaravel\VuetikLaravel::class;
    }
}
