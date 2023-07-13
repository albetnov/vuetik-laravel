<?php

namespace Vuetik\VuetikLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use Vuetik\VuetikLaravel\Factories\ContentFactory;

/**
 * @see \Vuetik\VuetikLaravel\VuetikLaravel
 *
 * @method static void routes()
 * @method static ContentFactory parseJson()
 * @method static ContentFactory parse()
 */
class VuetikLaravel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Vuetik\VuetikLaravel\VuetikLaravel::class;
    }
}
