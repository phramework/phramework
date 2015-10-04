<?php

namespace Examples\JSONAPI\APP\Models;

use \Phramework\Models\Database;
use \Phramework\Models\Validate;
use \Phramework\JSONAPI\Relationship;

class TestComment extends \Phramework\JSONAPI\Model
{
    protected static $validationModel = [
        'id'      => ['type' => Validate::TYPE_UINT],
        'test_id' => ['type' => Validate::TYPE_UINT, Validate::REQUIRED]
    ];

    public static function getByTest($testId)
    {
        return Database::executeAndFetchAllArray(
            'SELECT `id` FROM `test_comment`
            WHERE `test_id` = ?',
            [$testId],
            self::getCast()
        );
    }
}
