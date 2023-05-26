<?php

use Xaamin\Fractal\Facades\Fractal;

if (!function_exists('fractal')) {
    /**
     * @param null|mixed $data
     * @param null|callable|\League\Fractal\TransformerAbstract $transformer
     * @param null|\League\Fractal\Serializer\SerializerAbstract $serializer
     *
     * @return \Xaamin\Fractal\Fractal
     */
    function fractal($data = null, $transformer = null, $serializer = null)
    {
        return Fractal::make($data, $transformer, $serializer);
    }
}
