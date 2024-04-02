<?php
/** @noinspection PhpUndefinedFunctionInspection */

namespace kevinoo\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use kevinoo\GraphQL\Filters\GenericFilters;


trait PipelineFiltersTrait
{
    public function resolveFiltersPipeline( array $args ): Builder
    {
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

    /**
     * Return a builder instance using an Eloquent model
     * @param array $args
     * @return Builder
     */
    abstract public function resolveModelBuilder( array $args ): Builder;

    /**
     * Returns map of filters to database columns
     * @return array
     */
    abstract protected function getGenericFiltersKeys(): array;

    /**
     * Returns list of filter's class to add to Pipeline
     * @return array
     */
    protected function getPipelineFiltersSteps(): array
    {
        return [
            GenericFilters::class,
        ];
    }

}
