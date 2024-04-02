<?php

namespace kevinoo\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use kevinoo\GraphQL\Filters\GenericFilters;


trait PipelineFiltersTrait
{
    public function resolveFiltersPipeline( array $args ): Builder
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        [$builder] = app(Pipeline::class)
            ->send([
                $this->resolveModelBuilder($args),
                $this->getGenericFiltersKeys(),
                $args['filters'] ?? [],
            ])
            ->through($this->getPipelineSteps())
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
    protected function getGenericFiltersKeys(): array
    {
        return [
            GenericFilters::class,
        ];
    }

    /**
     * Returns list of filter's class to add to Pipeline
     * @return array
     */
    abstract public function getPipelineSteps(): array;

}
