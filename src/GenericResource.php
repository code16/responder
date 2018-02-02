<?php

namespace Code16\Responder;

use Illuminate\Http\Resources\Json\Resource;
use Code16\Responder\Exceptions\SerializationException;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Generic Resource class for handling payload
 * that have already been transform as array
 */
class GenericResource extends Resource
{
    protected $transformer = null;

    protected $statusCode;

    public function setStatusCode(int $status)
    {
        $this->statusCode = $status;
    }

    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
    }

    public function toArray($request)
    {
        $payload = $this->transformer
            ? $this->transformer->transform($this->resource)
            : $this->resource;

        if($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        }

        if(! is_array($payload)) {
            throw new SerializationException('Payload is not an array.');
        }
        
        return $payload;
    }

    /**
     * Calculate the appropriate status code for the response.
     *
     * @return int
     */
    protected function calculateStatus()
    {
        return $this->statusCode ? $this->statusCode : 200;
    }
}
