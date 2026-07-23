<?php

namespace App\Resource\Pagination;


class PageFactory
{

    public function create(
        int $page,
        int $limit
    ): Page
    {
        return new Page(
            $page,
            $limit
        );
    }
}
