<?php

namespace Code16\Responder;

use Illuminate\Support\Facades\Facade;

class Responder extends Facade
{
    protected static function getFacadeAccessor() 
    { 
        return 'responder'; 
    }
}
