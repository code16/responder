<?php

namespace Code16\Responder\Tests\Stubs\Actions;

use Code16\Responder\Tests\Stubs\Planet;
use Code16\Responder\Tests\Stubs\Generators\PlanetGenerator;

class ShowPlanet
{
    protected $planetGenerator;

    public function __construct(PlanetGenerator $planetGenerator)
    {
        $this->planetGenerator = $planetGenerator;
    }

    public function show($id) : Planet
    {
        $planet = $this->planetGenerator->generate();
        $planet->id = $id;
        return $planet;
    }

}
