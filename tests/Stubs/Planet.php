<?php

namespace Code16\Responder\Tests\Stubs;

use Ramsey\Uuid\Uuid;
use Illuminate\Contracts\Support\Arrayable;

class Planet implements Arrayable
{
    public $id;

    public $name;

    public $mass;

    public $distance;

    public $discoveredAt;
    
    public function __construct()
    {
        $this->id = Uuid::uuid1()->toString();
    }

    public function toArray()
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "mass" => $this->mass,
            "distance" => $this->distance,
            "discovered_at" => $this->discovered_at->toIso8601String(),
        ];  
    }
}
