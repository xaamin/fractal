<?php

namespace Xaamin\Fractal\Tests;

use ReflectionProperty;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Xaamin\Fractal\Fractal;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->resetDefaultSerializer();
    }

    protected function tearDown(): void
    {
        $this->resetDefaultSerializer();
    }

    private function resetDefaultSerializer(): void
    {
        (new ReflectionProperty(Fractal::class, 'serializer'))->setValue(null, null);
    }
}
