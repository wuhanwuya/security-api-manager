<?php

namespace Hso\TestApi\Test;

use Hso\TestApi\SecurityApiMangerServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            SecurityApiMangerServiceProvider::class,
        ];
    }
}