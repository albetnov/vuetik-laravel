<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::setRoutes(new \Illuminate\Routing\RouteCollection());
});

it('Registered route successfully', function () {
    \Vuetik\VuetikLaravel\Facades\VuetikLaravel::routes();
    expect(Route::getRoutes()->getRoutesByMethod(['POST'])['POST']['upload-img'])
        ->toBeInstanceOf(\Illuminate\Routing\Route::class);
});
