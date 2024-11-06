<?php

use Xaamin\Fractal\Facades\Fractal;

if (!function_exists('fractal')) {
    /**
     * @param null|mixed $data
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param null|string $resourceKey
     *
     * @return \Xaamin\Fractal\Fractal
     */
    function fractal($data = null, $transformer = null, $resourceKey = null)
    {
        return Fractal::make($data, $transformer, $resourceKey);
    }
}
