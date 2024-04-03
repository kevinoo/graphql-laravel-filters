# kevinoo/graphql-laravel-filters
This library extends the functionalities of "rebing/graphql-laravel," providing a comprehensive solution for managing pagination and applying advanced filters to GraphQL queries in Laravel applications.

## Installation
Use Composer to install the library:
```bash
composer require kevinoo/graphql-laravel-filters
```

## Usage
To utilize pagination in GraphQL queries, simply extend the AbstractPaginateQuery class provided by this library and use it in query resolvers.
```php
use kevinoo\GraphQL\Queries;

class MyCustomModelQuery extends AbstractPaginateQuery 
{
//    public const GENERIC_FILTERS = true;
//    public const TRASHED_FILTER = true;
//    public const MAX_LIMIT_RESULTS = 1000;

    protected $attributes = [
        'name' => 'Name of Query',
    ];

    public function getGraphQLType(): string
    {
        return 'Your GraphQL Type';
    }

    public function args(): array
    {
        return parent::args() + [
            'filters' => [
                'type' => GraphQL::type('Your GraphQL FilterInput'),
            ],
        ];
    }

    protected function resolveModelBuilder( array $args ): Builder
    {
        $builder = YourModel::query();

        $order_by = ($args['orders'] ?? []) ?: ['domain'=>'ASC'];
        foreach( $order_by as $column => $direction ){
            $builder->orderBy($column,$direction);
        }

        return $builder;
    }

    protected function getGenericFiltersKeys(): array
    {
        return [
            'your_input_key_1' => 'model_attribute_1', // Input value can be string, int, boolean or array
            'your_input_key_2' => new AdvancedSearch('model_attribute_to_apply_advanced_search'),
            'your_input_key_3_array' => 'model_attribute_2',
        ];
    }

    protected function getPipelineFiltersSteps(): array
    {
        return parent::getPipelineFiltersSteps() + [
            // Add your other custom filters class
        ];
    }
}
```

## Contributing
You are welcome to contribute to the project!
