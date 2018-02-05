<?php

namespace Code16\Responder\Tests;

use Orchestra\Testbench\TestCase;
use Code16\Responder\ResponderFactory;

abstract class ResponderTestCase extends TestCase
{
    protected function router()
    {
        return $this->app->make('router');
    }

    protected function responder()
    {
        return $this->app->make(ResponderFactory::class);
    }

    protected function getPackageProviders($app)
    {
        return ['Code16\Responder\ResponderServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Responder' => 'Code16\Responder\Responder'
        ];
    }
}
