<?php

namespace Code16\Responder\Tests\Stubs;

use Exception;
use Illuminate\Support\MessageBag;
use Code16\Responder\Interfaces\HasMessageBag;

class CustomValidationException extends Exception implements HasMessageBag
{
    protected $code = 422;

    protected $errors;

    public function __construct(MessageBag $errors)
    {
        $this->errors = $errors;
    }

    public function getMessageBag() : MessageBag
    {
        return $this->errors;
    }
}