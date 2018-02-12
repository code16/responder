<?php

namespace Code16\Responder\Tests\Stubs\Actions;

use Illuminate\Http\Request;
//use Illuminate\Foundation\Validation\ValidateRequest;
use Code16\Responder\Tests\Stubs\Planet;
use Carbon\Carbon;

class CreatePlanet
{
    //use ValidateRequest;

    public function execute(Request $request) : Planet
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'mass' => 'required|float',
            'distance' => 'required|float',
        ]);

        $planet = new Planet;
        $planet->name = $request->get('name');
        $planet->mass = $request->get('mass');
        $planet->distance = $request->get('distance');
        $planet->discovered_at = Carbon::now();

        return $planet;        
    }

}
