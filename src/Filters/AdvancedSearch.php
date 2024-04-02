<?php

namespace kevinoo\GraphQL\Filters;


class AdvancedSearch
{
    protected string $column_name;
    protected bool $case_insensitive;

    public function __construct( string $column_name, bool $case_insensitive=false )
    {
        $this->column_name = $column_name;
        $this->case_insensitive = $case_insensitive;
    }


    ////////////////////////////////////////////////
    // Getters & Setters

    public function getColumnName(): string
    {
        return $this->column_name;
    }
    public function setColumnName(string $column_name): AdvancedSearch
    {
        $this->column_name = $column_name;
        return $this;
    }

    public function isCaseInsensitive(): bool
    {
        return $this->case_insensitive;
    }
    public function setCaseInsensitive( bool $case_insensitive ): AdvancedSearch
    {
        $this->case_insensitive = $case_insensitive;
        return $this;
    }

}
