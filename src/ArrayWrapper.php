<?php

namespace Code16\Responder;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

class ArrayWrapper implements ArrayAccess, Arrayable
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

    function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

}
