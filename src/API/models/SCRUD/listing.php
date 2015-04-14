<?php
namespace Phramework\API\models\SCRUD;
use Phramework\API\models\database;
use Phramework\API\models\validate;

/**
 * Provides varius helper functions for listing
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @subpackage API
 * @category models
 */
class listing{
    /**
     * Parse page
     * @global type $method
     * @param type $parameters
     * @param type $page
     * @return type
     */
    public static function parse_page($parameters, &$page) {
        if (isset($parameters['page'])) {
            validate::uint($parameters['page']);
            $page = intval( $parameters['page'] );
            return;
        }
        global $method;
        if (isset($parameters['id']) && $method == 'listing') {
            validate::uint($parameters['id']);
            $page = $parameters['id'];
        }
    }

    /**
     * Parse order by
     * @param type $parameters
     * @param type $order_whitelist
     * @param type $order
     */
    public static function parse_order_by( $parameters, $order_whitelist, &$order ) {
        if (isset($parameters['order_by']) && in_array($parameters['order_by'], $order_whitelist)) {
            $order['field'] = $parameters['order_by'];
            $order['default'] = FALSE;
        }
        if (isset($parameters['order_asc'])) {
            $order['asc'] = TRUE;
            $order['default'] = FALSE;
        }
        if (isset($parameters['order_desc'])) {
            $order['asc'] = FALSE;
            $order['default'] = FALSE;
        }
    }

    /**
     * Parse filter by
     * @param type $parameters
     * @param type $filter_whitelist
     * @return boolean
     */
    public static function parse_filter_by( $parameters, $filter_whitelist ) {
        $operator = OPERATOR_EQUAL;
        if ( isset($parameters['filter_by']) && in_array($parameters['filter_by'], $filter_whitelist) && isset($parameters['filter_value']) ) {
            //#! validate!
            if( isset( $parameters[ 'filter_operator' ] )  ){
                $operator = validate::operator( $parameters[ 'filter_operator' ] );
            }
            return ['field' => $parameters['filter_by'], 'value' => $parameters['filter_value'], 'operator' => $operator ];
        }
        return FALSE;
    }

    /**
     * Perform a listing query
     * @param type $page
     * @param type $model
     * @param type $order
     * @param type $filter
     * @return type
     */
    public static function listing_query( $page, $model, $order = FALSE, $filter = FALSE ){
        $table = $model[ 'table' ];
        $index = $model[ 'index' ];

        //Set limit query string
        $limit = ( ($page - 1) * ITEMS_PER_PAGE) . ',' . ITEMS_PER_PAGE;
        //Set order query string
        $order_value = ( $order ? '"' . $order[ 'field' ] . '" ' . ($order[ 'asc' ] ? 'ASC' : 'DESC') : " \"$table\".\"$index\" DESC" );
        //Set filter query string

        $filter_value = ' ';
        if( !$filter ){

        }else if( in_array( $filter[ 'operator' ], [ OPERATOR_EQUAL, OPERATOR_GREATER, OPERATOR_GREATER_EQUAL, OPERATOR_LESS, OPERATOR_LESS_EQUAL, OPERATOR_NOT_EQUAL ] ) ){
            $filter_value = 'WHERE "' . $table . '"."' . $filter[ 'field' ] . '" ' . $filter[ 'operator' ] . '  "' . $filter[ 'value' ] . '" ';
        }else if( in_array( $filter[ 'operator' ], [ OPERATOR_ISNULL, OPERATOR_NOT_ISNULL ] ) ){
            $filter_value = 'WHERE ' . $filter[ 'operator' ] . '("' . $table . '"."' . $filter[ 'field' ] . '")';
        }

        //ToDo When $revisions_type is set and true
        if( isset( $model[ 'listing_fields' ] ) ){
            $select_fields = '"' . implode( '","', $model[ 'listing_fields' ] ) . '"' ;
            //$select_fields = rtrim( $select_fields, ',`' );
        }else{
            $select_fields = " \"$table\".*";
        }
        //@TODO FIX
        //Query string
        //$data = database::ExecuteAndFetchAll("SELECT $select_fields" .
           // . 'FROM "' . $table . '"' . $filter_value . ' ORDER BY ' . $order_value . ' LIMIT ' . $limit );

        return $data;
    }

    /**
     * Return number of pages for a specific query
     * @param type $table
     * @param type $filter
     * @return type
     */
    public static function listing_query_pages( $table, $filter = FALSE ){
        //$filter_value = ($filter ? "WHERE `$table`.`" . $filter['field'] . '` = "' . $filter['value'] . '" ' : ' ');
         $filter_value = ' ';
        //Set filter query string
        if( !$filter ){

        }else if( in_array( $filter[ 'operator' ], [ OPERATOR_EQUAL, OPERATOR_GREATER, OPERATOR_GREATER_EQUAL, OPERATOR_LESS, OPERATOR_LESS_EQUAL, OPERATOR_NOT_EQUAL ] ) ){
            $filter_value = 'WHERE "'. $table . '"."' . $filter[ 'field' ] . '" ' . $filter[ 'operator' ] . '  "' . $filter[ 'value' ] . '" ';
        }else if( in_array( $filter[ 'operator' ], [ OPERATOR_ISNULL, OPERATOR_NOT_ISNULL ] ) ){
            $filter_value = 'WHERE ' . $filter[ 'operator' ] . "(\"$table\".\"" . $filter[ 'field' ] . '`)';
        }

        $check = database::ExecuteAndFetch("SELECT COUNT(*) AS count
        FROM $table " . $filter_value);
        return ceil( floatval( $check[ 'count' ] ) / ITEMS_PER_PAGE );
    }

    /**
     * Perform a quick search
     *
     * @param type $text
     * @param type $model
     * @param type $fields
     * @param type $order
     * @param type $page
     * @param type $pages
     * @return type
     * @throws RequestException
     */
    public static function quick_search( $text, $model, &$fields, $order = FALSE, $page = 1, &$pages = 0 ){
        if( empty( $text ) ){
            throw new RequestException( 'Empty search text' );
        }

        //Search model ( returned to caller )
        $fields = [];

        //Query fields array
        $fields_query = [];

        //Query binding parameters
        $p = [];

        //List all searchable fields
        $searchable_fields = [];
        foreach( $model[ 'fields' ] as $key => $value ){
            $searchable_fields[] = $key;
        }
        if( !$searchable_fields ){
            throw new RequestException( 'No searchable fields' );
        }

        $table = $model[ 'table' ];
        $index = $model[ 'index' ];

        //Hack
        $text = html_entity_decode( $text );

        //Decode html unsafe text used in regular expressions
        $text_unsafe = html_entity_decode( $text );

        $operators = implode( '|', [ OPERATOR_EQUAL, OPERATOR_NOT_EQUAL, OPERATOR_GREATER, OPERATOR_GREATER_EQUAL, OPERATOR_LESS, OPERATOR_LESS_EQUAL ] );
        if( in_array( 'searchable', $model[ 'fields' ][ $index ] ) && preg_match( '/^(#|' . $operators . ')[ ]{0,1}(\d+)$/', $text_unsafe, $matches ) ){ // $operator $value where field is model's index
            //Replace # with = operator ( match #id )
            if( $matches[ 1 ] == '#' ){
                $matches[ 1 ] = '=';
            }
            $operator = validate::operator( $matches[ 1 ] );

            $fields_query[]  = "\"$index\" $operator ? ";

            //$matches[2] contains the id
            $p[] = $matches[ 2 ];
            $fields[ $index ] = [ 'operator' => $operator, 'value' =>  $matches[2] ];
        }else if( preg_match( '/^(' . implode( '|', $searchable_fields ) . ')[ ]{0,1}(' . $operators . ')[ ]{0,1}([\w+ :-]+)$/', $text_unsafe, $matches ) ){ //Match $field $operator $value

            $field = $matches[ 1 ];

            $operator = validate::operator( $matches[ 2 ] );

            $fields_query[]  = "\"$field\" $operator ? ";

            //$matches[3] contains the id
            $p[] = $matches[ 3 ];
            $fields[ $field ] = [ 'operator' => $operator, 'value' =>  $matches[ 3 ] ];
        }else if( preg_match( '/^(' . implode( '|', $searchable_fields ) . ')[ ]{0,1}(LIKE|NOT LIKE)[ ]{0,1}(\w+)$/', $text_unsafe, $matches ) ){ //Match $field LIKE $value

            $field = $matches[ 1 ];
            $operator = validate::operator( $matches[ 2 ] );
            $fields_query[]  = "\"$field\" $operator ? ";

            $p[] = '%' . $matches[ 3 ] . '%';
            $fields[ $field ] = [ 'operator' => $operator, 'value' =>  $matches[ 3 ] ];
        }else if( preg_match( '/^(' . implode( '|', $searchable_fields ) . ')[ ]{0,1}(ISNULL|!ISNULL)$/', $text_unsafe, $matches ) ){ //Match $field ISNULL

            $field = $matches[ 1 ];
            $operator = validate::operator( $matches[ 2 ] );
            $fields_query[]  = "$operator( \"$field\" )";

            //$p[] = '%' . $matches[ 3 ] . '%';
            $fields[ $field ] = [ 'operator' => $operator ];
        }else{
            //Find all searchable fields except searchable-noquick
            foreach( $searchable_fields as $key ){
                if( !in_array( 'searchable-noquick', $model[ 'fields' ][ $key ] ) ){
                    $fields_query[]  = "\"$key\" LIKE ? ";
                    $p[] = '%' . $text . '%';

                    $fields[ $key ] = [ 'operator' => OPERATOR_LIKE, 'value' =>  $text ];
                }
            }
        }

        //Set limit query string
        $limit = ( ($page - 1) * ITEMS_PER_PAGE) . ',' . ITEMS_PER_PAGE;

        $order_string = ( $order ? '"' . $order[ 'field' ] . '" ' . ( $order[ 'asc' ] ? 'ASC' : 'DESC' ) : " \"$table\".\"$index\" DESC" );

        if( isset( $model[ 'listing_fields' ] ) ){
            $select_fields = '"' . implode( '`,`', $model[ 'listing_fields' ] ) . '"';
            //$select_fields = rtrim( $select_fields, ',`' );
        }else{
            $select_fields = " \"$table\".*";
        }

        $data = database::ExecuteAndFetchAll( "SELECT $select_fields FROM" . '"' . $table . '" WHERE " '
           . implode( 'OR' , $fields_query ) . ' ORDER BY ' . $order_string . " LIMIT $limit", $p );

        if( $page == 1 && count( $data ) < ITEMS_PER_PAGE ){
            $pages = 1;
        }else{
            $pages = database::ExecuteAndFetch( 'SELECT COUNT( "' . $table . '"."' . $index . '" ) AS count FROM "' . $table
                . '" WHERE ' . implode( 'OR' , $fields_query ) . ' ORDER BY ' . $order_string, $p );
            $pages = ceil( floatval( $pages[ 'count' ] ) / ITEMS_PER_PAGE );
        }

        return $data;
    }
}
