<?php

namespace Code16\Responder\Tests;

use Code16\Responder\Tests\Stubs\Actions\ShowPlanet;
use Code16\Responder\Tests\Stubs\Actions\ListPlanets;
use Code16\Responder\Tests\Stubs\Planet;

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
        //dd($response->decodeResponseJson());
        $response->assertJsonStructure([
            'data',
        ]);
    }

    /** @test */
    function it_catches_exceptions_and_throw_error_with_http_error_code()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_throws_exceptions_when_no_error_code_is_set()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_returns_an_illuminate_resource_objects_by_default()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_uses_a_custom_transformer_object_if_provided()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_returns_a_custom_http_status_code_if_provided()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    function it_respond_with_pagination_metadata_if_a_paginator_is_return()
    {
        $this->markTestIncomplete();
    }
}
