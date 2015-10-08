<?php

namespace Examples\JSONAPI\APP\Models;

use \Phramework\Models\Database;
use \Phramework\Validate\Validate;
use \Phramework\JSONAPI\Relationship;

class TestComment extends \Phramework\JSONAPI\Model
{
    protected static $type = 'test_comment';
    protected static $endpoint = 'test_comment';
    protected static $table = 'test_comment';

    public static function getValidationModel()
    {
        return [
            'id'      => ['type' => Validate::TYPE_UINT],
            'test_id' => ['type' => Validate::TYPE_UINT, Validate::REQUIRED]
        ];
    }

    public static function getRelationshipByTest($testId)
    {
        return Database::executeAndFetchAllArray(
            'SELECT `id` FROM `test_comment`
            WHERE `test_id` = ?',
            [$testId],
            self::getCast()
        );
    }

    /**
     * Get a single entry by id
     * @param int $id Resource's id
     * @return \stdClass|null
     */
    public static function getById($id)
    {
        $record = Database::executeAndFetch(
            'SELECT * FROM `test_comment`
            WHERE `id` = ?
            LIMIT 1',
            [$id],
            self::getCast()
        );

        return self::resource($record);
    }
}
