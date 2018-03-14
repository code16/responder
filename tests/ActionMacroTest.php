<?php

namespace Code16\Responder\Tests;

use Responder;
use Code16\Responder\Tests\Stubs\Actions\ShowPlanet;
use Code16\Responder\Tests\Stubs\Planet;


class ActionMacroTest extends ResponderTestCase
{
    /** @test */
    function we_can_use_the_route_macro_to_bind_route_to_actions()
    {
        $this->withoutExceptionHandling();
        $this->router()->action(
            'get', 
            'planet/{id}', 
            ShowPlanet::class, 
            function($request, $action, $id) {
                return $action->execute($id);
            }
        );

        $response = $this->get('/planet/1234');
        $response->assertStatus(200);
    }

}
