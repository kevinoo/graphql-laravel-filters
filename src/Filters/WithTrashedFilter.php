<?php

namespace kevinoo\GraphQL\Filters;

use Closure;
use Illuminate\Database\Query\Builder;


class WithTrashedFilter
{
    public function handle($parameters, Closure $next)
    {
        /** @var $builder Builder */
        [$builder, $filters_keys, $filters] = $parameters;

        if( !isset($filters['deleted']) ){
            $filters['deleted'] = false;
        }

        if( $filters['deleted'] ){
            /** @noinspection PhpUndefinedMethodInspection */
            $builder->withTrashed();
        }

        return $next([$builder, $filters_keys, $filters]);
    }
}
