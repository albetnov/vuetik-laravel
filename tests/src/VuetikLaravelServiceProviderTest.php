<?php

use Illuminate\Support\Facades\Blade;

it('can generate twitter script', function () {
    $bladeView = '@twitterScript';
    $compiledView = Blade::compileString($bladeView);

    expect($compiledView)
        ->toContain('<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>');
});
