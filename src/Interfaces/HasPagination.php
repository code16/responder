<?php

namespace Code16\Responder\Interfaces;

interface HasPagination
{
    public function setPage(int $page);

    public function setPageSize(int $page);
}
