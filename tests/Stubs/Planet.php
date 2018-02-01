<?php

namespace Code16\Responder\Tests\Stubs;

use Ramsey\Uuid\Uuid;

class Planet
{
    public $id;

    public $name;

    public $mass;

    public $distance;

    public $discoveredAt;
    
    public function __construct()
    {
        $this->id = Uuid::uuid1();
    }

}
