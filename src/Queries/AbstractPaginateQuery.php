<?php

namespace kevinoo\GraphQL\Queries;

use kevinoo\GraphQL\InputObject\OrderInputObjectType;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;


abstract class AbstractPaginateQuery extends Query
{
    use PipelineFiltersTrait;

    public const MAX_LIMIT_RESULTS = 1000;

//    protected $attributes = [
//        'name' => 'links',
//    ];
//    protected $middleware = [
//
//    ];

    public function type(): Type
    {
        return GraphQL::paginate($this->getGraphQLType());
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'The number of results to retrive (max is '. static::MAX_LIMIT_RESULTS .')',
            ],
            'page' => [
                'type' => Type::int(),
                'description' => 'The page to returns (when paginated)',
            ],
            'orders' => [
                'type' => new OrderInputObjectType($this->getGraphQLType(),$this->getOrderByFields()),
                'description' => 'Set of orders with which to retrieve data',
            ]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $page = $args['page'] ?? 1;
        $limit = min($args['limit'],static::MAX_LIMIT_RESULTS);

        $fields = $getSelectFields();

        return $this->resolveFiltersPipeline($args)
            ->with($fields->getRelations())
            ->select($fields->getSelect())
            ->paginate( $limit, ['*'], 'page', $page);
    }


    abstract public function getGraphQLType(): string;

    public function getOrderByFields(): array
    {
        return [
//            'column_key' => 'Description'
        ];
    }

}
