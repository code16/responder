<?php

namespace Code16\Responder;

use Illuminate\Support\ServiceProvider;

class ResponderServiceProvider extends ServiceProvider
{

    /**
     * Register package services
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('responder', function($app) {
            return $app->make(ResponderFactory::class);
        });
    }
}