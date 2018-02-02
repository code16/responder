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
}
