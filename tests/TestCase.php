<?php

namespace Hdaklue\PathBuilder\Tests;

use Hdaklue\PathBuilder\PathBuilderServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            PathBuilderServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaraPath' => \Hdaklue\PathBuilder\Facades\LaraPath::class,
        ];
    }
}
