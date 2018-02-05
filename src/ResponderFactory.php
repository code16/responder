<?php

namespace Code16\Responder;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Responsable;
use InvalidArgumentException;
use Code16\Responder\Interfaces\HasPagination;

class ResponderFactory
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function json($action = null)
    {
        $this->setupAction($action);

        return new JsonResponder($action);
    }

    public function action($action, callable $handler = null)
    {
        $this->setupAction($action);

        $responder = new JsonResponder($action);

        return $handler
            ? $responder->handle($handler)
            : $responder;
    }

    public function handle(callable $handler)
    {
        $responder = new JsonResponder();

        return $responder->handle($handler);
    }

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