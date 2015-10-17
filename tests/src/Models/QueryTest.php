<?php

namespace Phramework\Models;

class QueryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Query
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = Query::table('condition');
    }

    /**
     * @covers Phramework\Models\Query::limit
     */
    public function testLimit()
    {
        $q = $this->object->limit(5);

        $this->assertInstanceOf(Query::class, $q);
    }

    /**
     * @covers Phramework\Models\Query::get
     */
    public function testGet()
    {
        $this->object
            ->join('user_condition', 'user_condition.condition_id' , '=', 'condition.id')
            ->whereLiteral('id', '=', '1')
            ->whereLiteral('user_condition.status', '=', 'ENABLED')
            ->whereLiteral('status', '=', 'ENABLED')
            ->get(['*',  'id', 'condition.*', 'user_condition.status']);
    }

}
