<?php

namespace Code16\Responder\Tests\Stubs;

use ArrayAccess;
use Ramsey\Uuid\Uuid;
use Illuminate\Contracts\Support\Arrayable;

class Planet implements ArrayAccess, Arrayable
{
    public $id;

    public $name;

    public $mass;

    public $distance;

    public $discovered_at;
    
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

    function offsetExists($offset)
    {
        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "mass" => $this->mass,
            "distance" => $this->distance,
            "discovered_at" => $this->discovered_at->toIso8601String(),
        ];

        return isset($data[$offset]);
    }

    function offsetGet($offset)
    {
        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "mass" => $this->mass,
            "distance" => $this->distance,
            "discovered_at" => $this->discovered_at->toIso8601String(),
        ];

        return $data[$offset];
    }

    function offsetSet($offset, $value)
    {
        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "mass" => $this->mass,
            "distance" => $this->distance,
            "discovered_at" => $this->discovered_at->toIso8601String(),
        ];

        $data[$offset] = $value;
    }

    function offsetUnset($offset)
    {
        $data = [
            "id" => $this->id,
            "name" => $this->name,
            "mass" => $this->mass,
            "distance" => $this->distance,
            "discovered_at" => $this->discovered_at->toIso8601String(),
        ];

        unset($data[$offset]);
    }
}
