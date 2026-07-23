<?php

namespace App\Resource\Pagination;

class Page
{
    private int $page;
    private int $limit;
    private int $offset;

    public function __construct(int $page, int $limit)
    {
        $this->page = max(1, $page);
        $this->limit = max(1, $limit);
        $this->offset = ($this->page - 1) * $this->limit;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getPages(int $totalItems): int
    {
        return (int) ceil($totalItems / $this->limit);
    }
}
