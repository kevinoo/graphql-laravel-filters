<?php

namespace kevinoo\GraphQL\InputObject;

use GraphQL\Type\Definition\InputObjectType;
use Rebing\GraphQL\Support\Facades\GraphQL;


class OrderInputObjectType extends InputObjectType
{
    protected $attributes = [];

    public function __construct( string $class_name, array $additional_order_fields=[] )
    {
        $this->attributes['name'] = $class_name .'OrderInput';
        $this->attributes['description'] = '';

        // Is the class from extract fields
        $order_field_keys = array_keys(array_filter(GraphQL::type($class_name)->config['fields'](),static function($item){
            return empty($item['hidden_in_ordering']);
        }));

        if( !empty($additional_order_fields) ){
            $order_field_keys = array_merge($order_field_keys,array_keys($additional_order_fields));
        }

        $order_fields = [];
        foreach( $order_field_keys as $key ){
            $order_fields[$key] = [
                'description' => $additional_order_fields[$key] ?? '',
                'type' => GraphQL::type('FieldOrderBy'),
            ];
        }

        parent::__construct([
            'name' => $this->attributes['name'],
            'description' => $this->attributes['description'],
            'fields' => $order_fields,
        ]);
    }
}
