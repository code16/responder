<?php

namespace Code16\Responder;

use Exception;
use JsonSerializable;
use Code16\Responder\ResponderException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

class JsonResponder implements Responsable
{
    /**
     * Transformer
     * 
     * @var mixed
     */
    protected $transformer = null;

    /**
     * Headers to be sent on every response.
     * 
     * @var array
     */
    protected $headers = [];

    /**
     * Http status code
     * 
     * @var int
     */
    protected $statusCode = 200;
    
    /**
     * Action object
     * 
     * @var object
     */
    protected $action;

    /**
     * Request handler
     * 
     * @var callable
     */
    protected $handler;

    /**
     * The request object
     * 
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct($action = null)
    {
        $this->action = $action;
    }

    /**
     * Set a closure that will be executed by the responder
     * prior to sent the response back.
     * 
     * @param callable $handler
     * @return static
     */
    public function handle(callable $handler)
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * Set transformer for payload
     * `
     * @param mixed $transformer
     * @return static
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
        return $this;
    }

    /**
     * Call the action and return the response
     * 
     * @return JsonResponse
     */
    public function toResponse($request)
    {
        if(! $this->handler && ! is_callable($this->handler)) {
            throw new ErrorException("No handler set");
        }

        try {
            $payload = call_user_func_array($this->handler, [$this->action, $request]);
        }
        catch (Exception $e) {
            // We'll assume that if an Exception has an error code 
            // that is explicitely defined, we want to use it as an 
            // HTTP error code. if not we'll just let the 
            // Exception handler takes care of it. 
            $code = $e->getCode();

            if ($code == 0 || ! $this->isValidHttpStatusCode($code)) {
                throw $e;
            }
            
            return $this->setStatusCode($code)->respondWithError($e->getMessage());
        }

        return $this->handlePayload($payload);
        
    }

    protected function handlePayload($payload)
    {
        return is_array($payload) ? $this->buildResponse($payload) : $this->buildResponse($this->transform($payload));
    }

    /**
     * Return true if the value is a valid HTTP response code
     *
     * @param  int     $statusCode 
     * @return boolean             
     */
    protected function isValidHttpStatusCode(int $statusCode) : bool
    {
        return $statusCode >= 100 && $statusCode < 600;
    }

    /**
     * Use a transformer instance to process the payload return by the handler
     * 
     * @param  mixed $payload
     * @return Illuminate\Http\Json\Resource
     */
    protected function transform($payload) : array
    {
        if ($this->transformer !== null) {
            return $this->transformer->transform($payload);
        }

        if($payload instanceof Arrayable) {
            return $payload->toArray();
        }
        
        if($payload instanceof JsonSerialize) {
            return $payload->jsonSerialize();
        }

        throw new ResponderException("Cannot serialize object");
    }

    /**
     * Respond with error message.
     *
     * @param $message
     *
     * @return mixed
     */
    protected function respondWithError(string $message)
    {
        return $this->buildResponse([
            'error' => [
                'message' => $message,
                'code' => $this->getStatusCode(),
            ],
        ]);
    }

    /**
     * Set HTTP status code
     * 
     * @param int $code
     * @return static
     */
    public function setStatusCode(int $code)
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Return HTTP status code
     * 
     * @return integer
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }
    
    /**
     * Build response object
     * 
     * @param  array  $data 
     * @param  array  $headers
     * 
     * @return JsonResponse
     */
    protected function buildResponse(array $data, array $headers = [], $options = 0)
    {   
        $headers = array_merge($headers, $this->headers);

        return new JsonResponse($data, $this->statusCode, $headers, $options);
    }
}