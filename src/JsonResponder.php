<?php

namespace Code16\Responder;

use Code16\Responder\Exceptions\ResponderException;
use Code16\Responder\Interfaces\HasMessageBag;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;

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
    protected $statusCode;
    
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

    /**
     * Additional handler parameters
     * 
     * @var array
     */
    protected $parameters = [];

    /**
     * @param null $action
     */
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
     * Set additional parameters, mostly useful when called without
     * a controller from a route macro.
     *
     * @param array $parameters
     * @return JsonResponder
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
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
     * Call the action and return the response as
     * a laravel resource
     *
     * @param $request
     * @return Resource|JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function toResponse($request)
    {
        if(! $this->handler && ! is_callable($this->handler)) {
            throw new ErrorException("No handler set");
        }

        $parameters = array_merge(
            [$request, $this->action],
            $this->parameters
        );

        try {
            $payload = call_user_func_array($this->handler, array_values($parameters));
        }
        catch (Exception $e) {
            // We'll assume that if an Exception has an error code 
            // that is explicitly defined, we want to use it as an
            // HTTP error code. if not we'll just let the 
            // Exception handler takes care of it. 
            $code = $e->getCode();

            if (! $this->isValidHttpStatusCode($code)) {
                throw $e;
            }
            
            $this->setStatusCode($code);

            return $e instanceof HasMessageBag 
                ? $this->respondWithMessageBag($e->getMessageBag(), class_basename($e))
                : $this->respondWithError($e->getMessage(), class_basename($e));
        }   
        catch (ValidationException $e) {
            throw $e;
        }

        return $this->handlePayload($payload);
    }

    /**
     * Route payload to the corresponding JsonResponse builder
     *
     * @param mixed $payload
     * @return JsonResponse
     * @throws ResponderException
     */
    protected function handlePayload($payload)
    {
        if(is_null($payload)) {
            return $this->buildEmptyResponse();
        }

        if(is_bool($payload)) {
            return $this->buildEmptyResponse();
        }

        if(is_string($payload)) {
            return $this->buildStringResponse($payload);
        }

        return $this->isIlluminateResource($payload)
            ? $this->buildResponse($payload) 
            : $this->buildResponse($this->intoResource($payload));
    }

    /**
     * Return true if resource is an instance of an Illuminate resource
     * 
     * @param  mixed $payload
     * @return boolean       
     */
    protected function isIlluminateResource($payload)
    {
        return $payload instanceof JsonResource;
    }

    /**
     * Return true if the value is a valid HTTP response code
     *
     * @param int $statusCode
     * @return boolean             
     */
    protected function isValidHttpStatusCode($statusCode)
    {
        return is_int($statusCode) && $statusCode >= 100 && $statusCode < 600;
    }

    /**
     * Use any available transformation method to process
     * the payload returned by the handler.
     *
     * @param mixed $payload
     * @return JsonResource
     * @throws ResponderException
     */
    protected function intoResource($payload)
    {
        // Transformer are intended to singular resources,
        // so what to do with collections
        if ($this->transformer != null) {
            $payload = $this->transformPayload($payload);
        }

        if($payload instanceof Collection || $payload instanceof AbstractPaginator) {
            return is_array($payload->first()) 
                ?  new ResourceCollection($payload->map(function($item) {
                    return new ArrayWrapper($item);
                }))
                : new ResourceCollection($payload);
        }

        if($payload instanceof Arrayable) {
            return $this->instantiateJsonResource($payload);
        }
        
        if(is_array($payload)) {
            return $this->instantiateJsonResource(new ArrayWrapper($payload));
        }
        
        if($payload instanceof \JsonSerializable) {
            return $this->instantiateJsonResource($payload);
        }

        throw new ResponderException("Cannot serialize object");
    }

    /**
     * Return new Json Resource
     *
     * @param $payload
     * @return JsonResource
     */
    protected function instantiateJsonResource($payload)
    {
        return new JsonResource($payload);
    }

    /**
     * Transform payload(s) into an array using custom transformer
     * 
     * @param  mixed $payload
     * @return array|collection
     */
    protected function transformPayload($payload)
    {
        if($payload instanceof Collection) {
            return $this->transformCollectionPayload($payload);
        }

        if($payload instanceof AbstractPaginator) {
            return $this->transformPaginatorPayload($payload);
        }
            
        return $this->transformer->transform($payload);
    }

    protected function transformCollectionPayload($payload)
    {
        return $payload->map(function($item) {
            return new ArrayWrapper($this->transformer->transform($item));
        });
    }

    protected function transformPaginatorPayload($payload)
    {
        $payload = $payload->setCollection($payload->getCollection()->map(function($item) {
            return new ArrayWrapper($this->transformer->transform($item));
        }));

        return $payload;
    }

    /**
     * Respond with error message.
     *
     * @param string $message
     * @param string $title
     * @return mixed
     */
    protected function respondWithError($message, $title)
    {
        return $this->buildResponse([
            'errors' => [
                [
                    'detail' => $message,
                    'status' => $this->getStatusCode(),
                    'title' => $title,
                ],
            ],
        ]);
    }

    /**
     * Respond with error message.
     *
     * @param MessageBag $messageBag
     * @param string $title
     * @return mixed
     */
    protected function respondWithMessageBag($messageBag, $title)
    {
        return $this->buildResponse([
            'message' => $title,
            'errors' => $messageBag->toArray(),
        ]);
    }

    /**
     * Set HTTP status code
     * 
     * @param int $code
     * @return static
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * Return explicit HTTP status code if set, or 200 by default
     * 
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode ?: 200;
    }

    /**
     * Build response object
     *
     * @param mixed $data
     * @param array $headers
     * @param int $options
     * @return JsonResponse
     */
    protected function buildResponse($data, $headers = [], $options = 0)
    {   
        $headers = array_merge($headers, $this->headers);

        $response = $data instanceof JsonResource
            ? $data->toResponse($this->request)
            : new JsonResponse($data, $this->getStatusCode(), $headers, $options);

        $response->setStatusCode($this->getStatusCode());

        return $response;
    }

    /**
     * Build an empty response
     * 
     * @param  array   $headers
     * @param  integer $options
     * @return JsonResponse
     */
    protected function buildEmptyResponse($headers = [], $options = 0)
    {
        $headers = array_merge($headers, $this->headers);

        $statusCode = $this->statusCode ? 
            $this->statusCode : 
            204;

        $response = new JsonResponse([], $statusCode, $headers, $options);

        return $response;
    }

     /**
     * Build an empty response
     * 
     * @param  string  $content
     * @param  array   $headers
     * @param  integer $options
     * @return JsonResponse
     */
    protected function buildStringResponse($content, $headers = [], $options = 0)
    {
        $headers = array_merge($headers, $this->headers);

        $data = [
            'data' => $content,
        ];

        $response = new JsonResponse($data, $this->getStatusCode(), $headers, $options);

        return $response;
    }
}