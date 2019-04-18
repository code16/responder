<?php

namespace Code16\Responder;

use Code16\Responder\Interfaces\HasPagination;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ResponderFactory
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param null $action
     * @return JsonResponder
     */
    public function json($action = null)
    {
        $this->setupAction($action);

        return new JsonResponder($action);
    }

    /**
     * @param $action
     * @param callable|null $handler
     * @return JsonResponder
     */
    public function action($action, callable $handler = null)
    {
        $this->setupAction($action);

        $responder = new JsonResponder($action);

        return $handler
            ? $responder->handle($handler)
            : $responder;
    }

    /**
     * @param callable $handler
     * @return JsonResponder
     */
    public function handle(callable $handler)
    {
        $responder = new JsonResponder();

        return $responder->handle($handler);
    }

    /**
     * @param null $action
     */
    protected function setupAction($action = null)
    {
        if(is_null($action)) {
            return;
        }

        if(! is_object($action)) {
            throw new InvalidArgumentException("action argument should be an object");
        }

        if($action instanceof HasPagination) {
            $this->setPagination($action);
        }
    }

    /**
     * @param $action
     */
    protected function setPagination($action)
    {
        if($this->request->has('page')) {
            $action->setPage($this->request->get('page'));
        }

        if($this->request->has('per_page')) {
            $action->setPageSize($this->request->get('per_page'));
        }

        if($this->request->has('perPage')) {
            $action->setPageSize($this->request->get('perPage'));
        }
    }        
}