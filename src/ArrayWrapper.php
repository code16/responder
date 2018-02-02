<?php

namespace Code16\Responder;

use Illuminate\Contracts\Support\Arrayable;

class ArrayWrapper implements Arrayable
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return $this->data;
    }
}
