<?php

namespace Examples\JSONAPI\APP\Models;

use \Phramework\Models\Database;
use \Phramework\Validate\Validate;
use \Phramework\JSONAPI\Relationship;

class Test extends \Phramework\JSONAPI\Model
{
    protected static $type = 'test';
    protected static $endpoint = 'test';
    protected static $table = 'test';

    /*
     * Define validation model
     */
    public static function getValidationModel()
    {
        return [
            'id'      => ['type' => Validate::TYPE_UINT],
            'created_user_id' => ['type' => Validate::TYPE_UINT],
            'title' => [
                'type' => Validate::TYPE_TEXT,
                'min' => 2,
                'max' => 6,
                Validate::REQUIRED
            ]
        ];
    }

    public static function getRelationships()
    {
        return [
            'created' => new Relationship('created_user_id', 'user'),
            'comment' => new Relationship(
                'test_comment_id',
                'test_comment',
                Relationship::TYPE_TO_MANY,
                TestComment::class,
                'test_id'
            )
        ];
    }

    /**
     * Get collection of resources
     *
     * @return \stdClass[]
     */
    public static function get()
    {
        $records = Database::executeAndFetchAll(
            'SELECT * FROM `test`',
            [],
            self::getCast()
        );

        /*foreach ($records as &$record) {
            $record['test_comment_id'] = TestComment::getByTest($record['id']);

        }*/

        return self::collection($records);
    }

    /**
     * Get a single entry by id
     * @param int $id Resource's id
     * @return \stdClass|null
     */
    public static function getById($id)
    {
        $record = Database::executeAndFetch(
            'SELECT * FROM `test`
            WHERE `id` = ?
            LIMIT 1',
            [$id],
            self::getCast()
        );

        return self::resource($record);
    }

    /* //uncomment to overwrite
    public static function post($attributes)
    {

    }*/
}

//Test::setRelationships();
