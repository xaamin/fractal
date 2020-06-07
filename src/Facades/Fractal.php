<?php
namespace Xaamin\Fractal\Facades;

use Illuminate\Support\Facades\Facade;

class Fractal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Xaamin\Fractal\Fractal';
    }
}