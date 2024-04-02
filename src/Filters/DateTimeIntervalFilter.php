<?php

namespace kevinoo\GraphQL\Filters;


class DateTimeIntervalFilter
{
    protected string $column_name;

    public function __construct( string $column_name )
    {
        $this->column_name = $column_name;
    }


    ////////////////////////////////////////////////
    // Getters & Setters

    /**
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->column_name;
    }

    /**
     * @param string $column_name
     * @return static
     */
    public function setColumnName(string $column_name): DateTimeIntervalFilter
    {
        $this->column_name = $column_name;
        return $this;
    }

}
