<?php

namespace Code16\Responder\Tests\Stubs\Actions;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Code16\Responder\Interfaces\HasPagination;
use Code16\Responder\Tests\Stubs\Generators\PlanetGenerator;

class ListPlanets implements HasPagination
{
    protected $planetGenerator;

    protected $page;

    protected $pageSize = 15;

    public function __construct(PlanetGenerator $planetGenerator)
    {
        $this->planetGenerator = $planetGenerator;
    }

    public function setPage(int $page)
    {
        $this->page = $page;
    }

    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function execute()
    {
        return $this->page ? $this->returnAsPaginator() : $this->returnAsCollection();
    }

    protected function returnAsCollection()
    {
        return new Collection($this->generatePlanets(23));
    }

    protected function returnAsPaginator()
    {   
        $planets = new Collection($this->generatePlanets($this->pageSize));

        return new LengthAwarePaginator($planets, count($planets) * 5, $this->pageSize);
    }

    protected function generatePlanets(int $number) : array
    {
        $planets = [];
        for($x=0;$x<$number;$x++) {
            $planets[] = $this->planetGenerator->generate();
        }
        return $planets;
    }
}
