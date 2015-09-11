<?php

namespace Phramework\Models\SCRUD;

use Phramework\Models\Validate;
use Phramework\Models\Database;
use Phramework\Exceptions\Request;
use Phramework\Exceptions\NotFound;

/**
 * create model
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @category Models
 */
class Create
{
    const RETURN_RECORDS = 1;
    const RETURN_ID = 0;

    /**
     * Create a new entry method
     */
    public static function create($keys_values, $table, $return = self::RETURN_ID)
    {
        /* $table = $model[ 'table' ];
          $index = $model[ 'index' ];

          if ( in_array( 'created_type', $model ) ){ // && ( !isset( $keys_values[ 'created' ] ) || empty( $keys_values['created'] ) ) ){
          $keys_values[ 'created' ] = date( 'Y-m-d H:i:s' );
          }
          if( in_array( 'created_user_id_type', $model ) ){ // && ( !isset( $keys_values[ 'created_user_id' ] ) || empty( $keys_values[ 'created_user_id' ] ) ) ){
          $user = Util::check_permission( );

          $keys_values[ 'created_user_id' ] = $user[ 'id' ];
          }
          //Get unique fields
          foreach( $model[ 'fields' ] as $key => $value ){
          if( in_array( 'unique', $value ) && isset( $keys_values[ $key ] ) ){
          //Check per field if exists
          if( Database::execute( "SELECT "$table"."$index" FROM "$table" WHERE "$table"."$key" = ? LIMIT 1", [ $keys_values[ $key ] ] ) ){
          throw new RequestException( 'Unique field ' . $key . ' already exists at another entry' );
          }
          }
          } */
        $query_keys   = implode('" , "', array_keys($keys_values));
        $query_parameter_string = trim(str_repeat('?,', count($keys_values)), ',');
        $query_values = array_values($keys_values);

        $query = 'INSERT INTO ';

        if (is_array($table) &&
            isset($table['schema']) &&
            isset($table['table'])) {
            $query .= '"' . $table['schema'] . '"' . '."' . $table['table'] . '"';
        } else {
            $query .= '"' . $table . '"';
        }

        $query .= ' ("' . $query_keys . '") ' . "VALUES ($query_parameter_string )";

        $driver = \Phramework\Models\Database::get_db_driver();
        //Return inserted id
        if ($return == self::RETURN_ID) {
            if ($driver == 'postgresql') {
                $query .= ' RETURNING id';
            }
            return Database::executeLastInsertId($query, $query_values);
        //Return number of rows affected
        } else {
            if ($driver == 'postgresql') {
                $query .= 'RETURNING ' . '*';
            }

            return Database::executeAndFetch($query, $query_values);
        }
    }
}
