<?php
namespace Xaamin\Fractal\Facades\Laravel;

use Illuminate\Support\Facades\Facade;

class Fractal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Xaamin\Fractal\Transformer';
    }
}