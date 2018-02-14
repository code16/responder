<?php

namespace Code16\Responder\Interfaces;

use Illuminate\Support\MessageBag;

interface HasMessageBag
{
    public function getMessageBag() : MessageBag;
}
