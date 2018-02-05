<?php

namespace Code16\Responder\Tests;

use Code16\Responder\Tests\Stubs\Actions\ShowPlanet;
use Code16\Responder\Tests\Stubs\Actions\ListPlanets;
use Code16\Responder\Tests\Stubs\Planet;
use Code16\Responder\Tests\Stubs\PlanetTransformer;
use Code16\Responder\Tests\Stubs\PlanetNotFoundException;

class JsonResponderTest extends ResponderTestCase
{
    /** @test */
    function it_can_respond_a_singular_resource_when_returning_an_arrayable_item_from_an_action()
    {
        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            });
        });
        
        $response = $this->get('/planet/1234');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    /** @test */
    function it_throws_a_serialization_exception_when_returned_object_is_not_arrayable()
    {
        $this->app->bind(ShowPlanet::class, function($app) {
            return new class extends ShowPlanet {

                public function __construct()
                {}

                public function execute($id)
                {
                    return new \stdClass;
                }
            };
        });

        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            });
        });
        
        $response = $this->get('/planet/1234');
        $response->assertStatus(500);
    }

    /** @test */
    function it_can_respond_a_plural_resource()
    {
        $this->router()->get('planets', function(ListPlanets $listPlanet) {
            return $this->responder()->json($listPlanet)->handle(function($action) {
                return $action->execute();
            });
        });
        $response = $this->get('/planets');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
        //dd($response->getContent());
    }

    /** @test */
    function it_returns_a_paginated_json_response_when_page_is_set_on_query_string()
    {
        $this->router()->get('planets', function(ListPlanets $listPlanet) {
            return $this->responder()->json($listPlanet)->handle(function($action) {
                return $action->execute();
            });
        });
        $response = $this->withoutExceptionHandling()->get('/planets?page=2', ['Accept' => 'application/json']);
        $response->assertStatus(200);
        //
        //dd(json_decode($response->getContent()));
        $response->assertJsonStructure([
            'data',
            'meta',
            'links',
        ]);
    }

    /** @test */
    function it_catches_exceptions_and_throw_errors_with_correct_http_status_code()
    {
        $this->app->bind(ShowPlanet::class, function($app) {
            return new class extends ShowPlanet {
                public function __construct()
                {}

                public function execute($id)
                {
                    throw new PlanetNotFoundException("Planet does not exists");
                }
            };
        });

        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            });
        });

        $response = $this->get('/planet/1234');
        $response->assertStatus(404);
        $response->assertJson([
            "errors" => [
                [
                    "status" => 404,
                    "title" => "PlanetNotFoundException",
                    "detail" => "Planet does not exists",
                ],
            ],
        ]);
    }

    /** @test */
    function it_throws_exceptions_when_no_error_code_is_set()
    {
       $this->app->bind(ShowPlanet::class, function($app) {
            return new class extends ShowPlanet {
                public function __construct()
                {}

                public function execute($id)
                {
                    throw new \Exception("Something unexpected happened");
                }
            };
        });

        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            });
        });

        $response = $this->get('/planet/1234');
        $response->assertStatus(500);
    }

    /** @test */
    function it_uses_a_custom_transformer_object_if_provided()
    {
        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            })->setTransformer(new PlanetTransformer);
        });

        $response = $this->get('/planet/1234');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'extra',
            ],
        ]);
    }

    /** @test */
    function it_returns_a_custom_http_status_code_if_provided()
    {
        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            })->setTransformer(new PlanetTransformer)->setStatusCode(201);
        });

        $response = $this->get('/planet/1234');
        $response->assertStatus(201);
    }

    /** @test */
    function it_responds_with_a_default_204_on_null_or_boolean_response()
    {
        $this->app->bind(ShowPlanet::class, function($app) {
            return new class extends ShowPlanet {
                public function __construct()
                {}

                public function execute($id)
                {
                    return null;
                }
            };
        });

        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            });
        });

        $response = $this->get('/planet/1234');
        $response->assertStatus(204);

         $this->app->bind(ShowPlanet::class, function($app) {
            return new class extends ShowPlanet {
                public function __construct()
                {}

                public function execute($id)
                {
                    return true;
                }
            };
        });

        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            });
        });

        $response = $this->get('/planet/1234');
        $response->assertStatus(204);
    }

    /** @test */
    function user_can_customise_empty_response_coee()
    {
        $this->app->bind(ShowPlanet::class, function($app) {
            return new class extends ShowPlanet {
                public function __construct()
                {}

                public function execute($id)
                {
                    return null;
                }
            };
        });

        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            })->setStatusCode(207);
        });

        $response = $this->get('/planet/1234');
        $response->assertStatus(207);
    }

    /** @test */
    function it_handles_string_response()
    {
        $this->app->bind(ShowPlanet::class, function($app) {
            return new class extends ShowPlanet {
                public function __construct()
                {}

                public function execute($id)
                {
                    return "OK";
                }
            };
        });

        $this->router()->get('planet/{id}', function($id, ShowPlanet $showPlanet) {
            return $this->responder()->json($showPlanet)->handle(function($action) use($id) {
                return $action->execute($id);
            });
        });

        $response = $this->get('/planet/1234');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => 'OK',
        ]);
    }
}
