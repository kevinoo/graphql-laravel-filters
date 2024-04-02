<?php

namespace kevinoo\GraphQL\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;


class GenericFilters
{
    public function handle( $parameters, Closure $next)
    {
        /**
         * @var $builder        Builder
         * @var $filters_keys   array   Values returned from static::getGenericFiltersKeys()
         * @var $filters        array   GraphQL's request params
        */
        [ $builder, $filters_keys, $filters ] = $parameters;

        foreach( $filters_keys as $key => $column_name ){

            if( empty($filters[$key]) ){
                continue;
            }

            if( is_object($column_name)  ){
                if( get_class($column_name) === AdvancedSearch::class ){
                    $builder = $this->advancedFilter(
                        builder: $builder,
                        advancedSearchObject: $column_name,
                        advanced_value: $filters[$key]
                    );
                    continue;
                }
                if( get_class($column_name) === DateTimeIntervalFilter::class ){
                    $builder = $this->dateTimeIntervalFilter($builder, $column_name->getColumnName(),$filters[$key]['from']??null, $filters[$key]['to']??null);
                    continue;
                }
            }

            if( $column_name instanceof Closure ){
                /* Is a closure to change the Builder instance */
                $builder = $column_name($builder,$key,$filters[$key]);
                continue;
            }

            $this->simplyFilter($builder,$column_name,$filters[$key]);
        }

        return $next([$builder, $filters_keys, $filters]);
    }

    /**
     * Simply filter by a value or array. Strict comparison as "string === string"
     */
    protected function simplyFilter( $builder, $column_name, $value ): Builder
    {
        if( is_array($value) ){

            // Check NULL value in the values array
            if( in_array(null,$value,true) ){

                $builder->where(function($b) use ($column_name,$value){
                    $index = array_search(null,$value,true);
                    unset($value[$index]);

                    $b->whereNull($column_name);
                    if( !empty($value) ){
                        $b->orWhereIn($column_name,$value);
                    }

                    return $b;
                });

                return $builder;
            }

            $builder->whereIn($column_name,$value);
        }else{
            $builder->where($column_name,$value);
        }

        return $builder;
    }

    /**
     * Advanced filter using single string value
     *
     * String example => explanation
     * "my three strings" => Result must contain "my three strings"
     * This "string" is an "example" => Result must contain "string" and "example", optionally a word of: "this" or "is" or "an"
     * One two three => Results will contain at least one of words: "one" or "two" or "three"
     */
    protected function advancedFilter( $builder, AdvancedSearch $advancedSearchObject, $advanced_value ): Builder
    {
        return $builder->where(function($sub_builder) use ($advancedSearchObject,$advanced_value){

            if( str_contains($advanced_value,'"') ){
                preg_match_all('/"(?<keywords>[* \w-]+)"/',trim($advanced_value),$matches);
                $mandatory_keywords = $matches['keywords'] ?? [];

                if( !empty($mandatory_keywords) ){
                    $sub_builder->where(function($query) use ($advancedSearchObject,$mandatory_keywords,&$advanced_value){
                        foreach( $mandatory_keywords as $keyword ){

                            if( str_contains($keyword,'*') ){
                                $query = $this->whereILikeString($query,$advancedSearchObject,$keyword);
                            }else{
                                $query->where($advancedSearchObject->getColumnName(),$keyword);
                            }

                            $advanced_value = trim(str_replace("\"$keyword\"",'',$advanced_value));
                        }
                    });
                }
            }

            // Optional keyword
            $sub_builder->orWhere(function($query) use ($advancedSearchObject,$advanced_value){
                foreach( explode(' ',$advanced_value) as $keyword ){
                    $query = $this->whereILikeString($query,$advancedSearchObject,$keyword,'OR');
                }
            });

            return $sub_builder;
        });
    }

    protected function dateTimeIntervalFilter( $builder, string $column_name, $from, $to=null ): Builder
    {
        // Tip: used "whereRaw" query to support search inside a JSONB column
        if( $to === null ){
            $builder->whereRaw("$column_name >= '$from'");
        }else{
            $builder->whereRaw("$column_name BETWEEN '$from' AND '$to'");
        }

        return $builder;
    }

    protected function whereILikeString( Builder $builder, $advancedSearchObject, $value, $glue='AND' ): Builder
    {
        $method = ($glue === 'AND') ? 'where' : 'orWhere';

        if( $advancedSearchObject->isCaseInsensitive() || str_contains($value,'*') ){
            $builder->$method($advancedSearchObject->getColumnName(), 'ILIKE', str_replace('*','%',$value) );
        }else{
            $builder->$method($advancedSearchObject->getColumnName(),$value);
        }

        return $builder;
    }
}
