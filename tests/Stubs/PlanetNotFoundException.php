<?php

namespace Code16\Responder\Tests\Stubs;

use Exception;

class PlanetNotFoundException extends Exception
{
    protected $code = 404;
}
