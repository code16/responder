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

        // Add route action macro
        $router = $this->app->make('router');

        $app = $this->app;

        $router->macro('action', function ($method, $url, $action, $callback, $transformer = null) use($router, $app) {
            return $router->$method($url, function(...$parameters) use($action, $callback, $app, $transformer) {
                
                $action = $app->make($action);
                
                $responder = $app->make(ResponderFactory::class);

                return $transformer 
                    ? $responder->action($action, $callback)
                        ->setParameters($parameters)
                        ->setTransformer($transformer)
                    : $responder->action($action, $callback)
                        ->setParameters($parameters);
            });
        });
    }

}