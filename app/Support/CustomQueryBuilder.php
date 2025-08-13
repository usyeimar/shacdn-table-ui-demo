<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final class CustomQueryBuilder
{
    /**
     * Create a new JsonApiQueryBuilder instance.
     * Through this method we can add the jsonPaginate method to the QueryBuilder class
     */
    public function jsonPaginate(): Closure
    {
        return function () {
            /** @var Builder $this */
            return $this->paginate(
                $perPage = request('page.size', 15),
                $columns = ['*'],
                $pageName = 'page[number]',
                $page = request('page.number', 1)
            );
        };
    }
}
