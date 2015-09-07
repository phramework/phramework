<?php
namespace Phramework\Models\SCRUD;

use Phramework\Models\Validate;
use Phramework\Models\Database;
use Phramework\Exceptions\Request;
use Phramework\Exceptions\NotFound;

/**
 * update model
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @subpackage API
 * @category models
 */
class Update
{
    /**
     * Update an entry method
     */
    public static function update($id, $keys_values, $table, $index = 'id')
    {

        /*
        //Get unique fields
        foreach ($model['fields'] as $key => $value) {
            if (in_array('unique', $value) && isset($keys_values[$key])) {
                //Check per field if excists except it self
                if (Database::execute("SELECT "$table"."$index" FROM "$table" WHERE "$table"."$key" = ? AND "$table"."$index" != ? LIMIT 1", [ $keys_values[$key],
                        $id])) {
                    throw new request('Unique field ' . $key . ' already exists at another entry');
                }
            }
        }

        if ($entry) {
            $updated_fields_count = 0;
            foreach ($keys_values as $key => $value) {
                if ($value != $entry[$key]) {
                    ++$updated_fields_count;
                }
            }
            if (!$updated_fields_count) {
                throw new request('No changes made');
            }
        }*/

        /*
        //Complete type specific fields
        if (in_array('updated_type', $model)) { // && ( !isset( $keys_values[ 'updated' ] ) || empty( $keys_values['updated'] ) ) ){
            $keys_values['updated'] = date('Y-m-d H:i:s');
        }
        if (in_array('updated_user_id_type', $model)) { // && ( !isset( $keys_values[ 'updated_user_id' ] ) || empty( $keys_values[ 'updated_user_id' ] ) ) ){
            $user                             = Util::check_permission();
            $keys_values['updated_user_id'] = $user['id'];
        }*/
        $query_keys = implode('" = ?,"', array_keys($keys_values));
        $query_values = array_values($keys_values);
        //Push id to the end
        $query_values[] = $id;

        $query = 'UPDATE ';

        $table_name = '';
        if (is_array($table) &&
            isset($table['schema']) &&
            isset($table['table'])) {
            $table_name = '"' . $table['schema'] . '"'
                .'."' . $table['table'] . '"';
        } else {
            $table_name = '"' . $table . '"';
        }

        $query.= $table_name;

        $query .= ' SET "' . $query_keys . '" = ? '
            . 'WHERE ' . $table_name . '."' . $index . '" = ?';

        //Return number of rows affected
        $result = Database::execute($query, $query_values);

        return $result;
    }
}
