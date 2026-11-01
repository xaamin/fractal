<?php

use Illuminate\Container\Container;
use Xaamin\Fractal\Fractal;
use Xaamin\Fractal\Facades\Fractal as FractalFacade;

test('helper function uses facade', function () {
    $app = new Container;
    $app->bind(Fractal::class, fn () => new Fractal);
    FractalFacade::setFacadeApplication($app);

    try {
        $this->assertInstanceOf(Fractal::class, fractal(['id' => 1]));
        $this->assertSame(['id' => 1], fractal(['id' => 1])->toArray());
    } finally {
        FractalFacade::clearResolvedInstances();
        FractalFacade::setFacadeApplication(null);
    }
});
