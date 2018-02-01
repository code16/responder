<?php

namespace Code16\Responder\Tests\Stubs\Generators;

use Code16\Responder\Tests\Stubs\Planet;
use Faker\Generator as Faker;

class PlanetGenerator
{
    protected $faker;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    public function generate() : Planet
    {
        $faker = $this->faker;

        $planet = new Planet;
        $planet->name = $faker->randomElement(['Glieser', 'Kepler', 'Trappist'])."-".$faker->numberBetween(1,999).$faker->randomElement(['A','B','C','D','E','F','G','H','I']);
        $planet->mass = $faker->randomFloat(3, 0.001, 100),
        $planet->distance = $faker->randomFloat(2, 0.5, 1000),
        $planet->discovered_at = $faker->dateTimeThisCentury(),

        return $planet;
    }



}