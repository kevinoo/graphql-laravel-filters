<?php

namespace kevinoo\GraphQL\Enums;

use Rebing\GraphQL\Support\EnumType;


class FieldOrderByEnum extends EnumType
{
    protected $attributes = [
        'name' => 'FieldOrderBy',
        'description' => 'The direction of the order',
        'values' => [
            'ASC' => 'ASC',
            'DESC' => 'DESC',
        ],
    ];
}
