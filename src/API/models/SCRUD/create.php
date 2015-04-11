<?php

namespace Phramework\API\models\SCRUD;

use Phramework\API\models\validate;
use Phramework\API\models\database;
use Phramework\API\exceptions\request;
use Phramework\API\exceptions\not_found;

/**
 * create model
 * @author Xenophon Spafaridis <nohponex@gmail.com>
 */
class create {

    const RETURN_RECORDS = 1;
    const RETURN_ID = 0;

    /**
     * Create a new entry method
     */
    public static function create($keys_values, $table, $return = self::RETURN_ID) {
        /* $table = $model[ 'table' ];
          $index = $model[ 'index' ];

          if ( in_array( 'created_type', $model ) ){ // && ( !isset( $keys_values[ 'created' ] ) || empty( $keys_values['created'] ) ) ){
          $keys_values[ 'created' ] = date( 'Y-m-d H:i:s' );
          }
          if( in_array( 'created_user_id_type', $model ) ){ // && ( !isset( $keys_values[ 'created_user_id' ] ) || empty( $keys_values[ 'created_user_id' ] ) ) ){
          $user = util::check_permission( );

          $keys_values[ 'created_user_id' ] = $user[ 'id' ];
          }
          //Get unique fields
          foreach( $model[ 'fields' ] as $key => $value ){
          if( in_array( 'unique', $value ) && isset( $keys_values[ $key ] ) ){
          //Check per field if exists
          if( database::Execute( "SELECT "$table"."$index" FROM "$table" WHERE "$table"."$key" = ? LIMIT 1", [ $keys_values[ $key ] ] ) ){
          throw new RequestException( 'Unique field ' . $key . ' already exists at another entry' );
          }
          }
          } */
        $query_keys   = implode('" , "', array_keys($keys_values));
        $query_parameter_string = trim(str_repeat('?,', count($keys_values)), ',');
        $query_values = array_values($keys_values);

        $query = 'INSERT INTO "' . $table . '" ("' . $query_keys . '") '
            . "VALUES ($query_parameter_string )";
        //Return inserted id
        if($return == self::RETURN_ID) {
            $driver = \API\API::get_db_driver();
            if ($driver == 'postgresql') {
                $query .= ' RETURNING id';
            }
            return database::ExecuteLastInsertId($query, $query_values);
        //Return number of rows affected
        } else {
            return database::Execute($query, $query_values);
        }
    }

}
