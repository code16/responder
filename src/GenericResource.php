<?php

namespace Code16\Responder;

use Illuminate\Http\Resources\Json\Resource;
use Code16\Responder\Exceptions\SerializationException;

/**
 * Generic Resource class for handling payload
 * that have already been transform as array
 */
class GenericResource extends Resource
{
    protected $transformer = null;

    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
    }

    public function toArray($request)
    {
        $payload = $this->transformer
            ? $this->transformer->transform($this->resource)
            : $this->resource;

        if(! is_array($payload)) {
            throw new SerializationException('Payload is not an array.');
        }
        
        return $payload;
    }
}
