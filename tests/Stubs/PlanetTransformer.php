<?php

namespace Code16\Responder\Tests\Stubs;

class PlanetTransformer
{
    public function transform($planet)
    {
        return [
            "id" => $planet->id,
            "name" => $planet->name,
            "mass" => $planet->mass,
            "distance" => $planet->distance,
            "discovered_at" => $planet->discovered_at->toIso8601String(),
        ];
    }
}
