<?php

namespace kevinoo\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;
use kevinoo\GraphQL\Filters\GenericFilters;
use kevinoo\GraphQL\Filters\WithTrashedFilter;
use kevinoo\GraphQL\InputObject\OrderInputObjectType;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;


abstract class AbstractPaginateQuery extends Query
{
    /**
     * Enable GenericFilter step in the pipeline flow.
     * @var bool|string True|false to enable GenericFilters class, can be class name (string) with the class to use. Otherwise, (false) to deactivate this feature.
     * @see GenericFilters
     */
    public const GENERIC_FILTERS = true;

    /**
     * Enable WithTrashedFilter step in the pipeline flow.
     * @var bool|string True|false to enable WithTrashedFilter class, can be class name (string) with the class to use. Otherwise, (false) to deactivate this feature.
     * Warning: see README.md how to use this, must be existing in input query params the key "deleted" (bool).
     * @see WithTrashedFilter
     */
    public const TRASHED_FILTER = true;

    /**
     * Define the limit of data to return per page.
     * Use -1 value to return all results
     * @const integer
    */
    public const MAX_LIMIT_RESULTS = 1000;


    public function type(): Type
    {
        return GraphQL::paginate($this->getGraphQLType());
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'The number of results to retrive'.
                    (!empty(static::MAX_LIMIT_RESULTS) && static::MAX_LIMIT_RESULTS > 0) ? ' (maximum is '. static::MAX_LIMIT_RESULTS .')' : '',
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
        $limit = min($args['limit'],static::MAX_LIMIT_RESULTS ?? -1 );

        $fields = $getSelectFields();

        return $this->resolveFiltersPipeline($args)
            ->with($fields->getRelations())
            ->select($fields->getSelect())
            ->paginate( $limit, ['*'], 'page', $page);
    }


    /**
     * Return the GraphQL Type to return by the paginator
     * @return string
     * @see self::type()
     */
    abstract protected function getGraphQLType(): string;

    /**
     * Return a builder instance using an Eloquent model
     * @param array $args
     * @return Builder
     */
    abstract protected function resolveModelBuilder( array $args ): Builder;

    /**
     * Returns map of filters to database columns
     * @return array
     */
    abstract protected function getGenericFiltersKeys(): array;


    protected function getOrderByFields(): array
    {
        return [
//            'column_key' => 'Description'
        ];
    }

    /**
     * Returns list of filter's class to add to Pipeline
     * @return array
     */
    protected function getPipelineFiltersSteps(): array
    {
        $filters = [];

        if( !empty(static::GENERIC_FILTERS) ){
            $filters[] = match(true){
                is_bool(static::GENERIC_FILTERS) => GenericFilters::class,
                is_string(static::GENERIC_FILTERS) => static::GENERIC_FILTERS,
                default => throw new InvalidArgumentException('Value of const GENERIC_FILTERS is invalid. Must be bool or string'),
            };
        }

        if( static::TRASHED_FILTER ){
            $filters[] = match(true){
                is_bool(static::TRASHED_FILTER) => WithTrashedFilter::class,
                is_string(static::TRASHED_FILTER) => static::TRASHED_FILTER,
                default => throw new InvalidArgumentException('Value of const TRASHED_FILTER is invalid. Must be bool or string'),
            };
        }

        return $filters;
    }


    protected function resolveFiltersPipeline( array $args ): Builder
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        [$builder] = app(Pipeline::class)
            ->send([
                $this->resolveModelBuilder($args),
                $this->getGenericFiltersKeys(),
                $args['filters'] ?? [],
            ])
            ->through($this->getPipelineFiltersSteps())
            ->thenReturn();

        return $builder;
    }
}
