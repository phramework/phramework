<?php

namespace Phramework\Examples\JSONAPI\APP\Models;

use \Phramework\Models\Database;
use \Phramework\Validate\Validate;
use \Phramework\JSONAPI\Relationship;

class User extends \Phramework\JSONAPI\Model
{
    protected static $type = 'user';
    protected static $endpoint = 'user';
    protected static $table = 'user';

    /**
     * Get a single entry by id
     * @param int $id Resource's id
     * @return \stdClass|null
     */
    public static function getById($id)
    {
        $record = Database::executeAndFetch(
            'SELECT id, username
            FROM `user`
            WHERE `id` = ?
            LIMIT 1',
            [$id],
            self::getCast()
        );

        return self::resource($record);
    }
}
