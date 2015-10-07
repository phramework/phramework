<?php

namespace Examples\JSONAPI\APP\Models;

use \Phramework\Models\Database;
use \Phramework\Validate\Validate;
use \Phramework\JSONAPI\Relationship;

class TestComment extends \Phramework\JSONAPI\Model
{
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
}
