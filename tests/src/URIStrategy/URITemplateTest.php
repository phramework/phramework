<?php

namespace Phramework\URIStrategy;

use \Phramework\Phramework;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-09-18 at 02:24:31.
 * @todo check request method
 */
class URITemplateTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var URITemplate
     */
    protected $object;

    /**
     * Define templates for testing
     * @var array
     */
    protected $testTemplates = [
        ['book/', 'BookController', 'GET', Phramework::METHOD_ANY],
        ['author/', 'AuthorController', 'POST', Phramework::METHOD_ANY]
    ];

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $this->object = new URITemplate($this->testTemplates);

        $_SERVER['QUERY_STRING'] = 'spam=true&ok=false';
        $_SERVER['REQUEST_URI'] = '/api/book/1?spam=true&ok=false';

        $_SERVER['SCRIPT_NAME'] = '/api/index.php';
    }

    public function testSuccessProvider()
    {
        return [
            'simple example check' => [
                'book/', 'book', []
            ],
            'check parameter' =>    [
                'book/{id}', 'book/1', ['id' => '1']
            ],
            'check alphanumeric parameter' => [
                'book/{id}', 'book/abcdefABCDE0123', ['id' => 'abcdefABCDE0123']
            ],
            'check relashionship' => [
                'book/{id}/author', 'book/1/author', ['id' => '1']
            ],
            'check two level' => [
                'book/author', 'book/author', []
            ],
            'check two level with alphanumeric parameter' => [
                'book/author/{id}', 'book/author/500abc', ['id' => '500abc']
            ],
            'check relashionship\'s multiple parameters' => [
                'book/{id}/author/{author_id}',
                'book/3/author/2',
                ['id' => '3', 'author_id' => '2']
            ]
        ];
    }

    public function testFailureProvider()
    {
        return [
            'check relashionship' => [
                'book/', 'books'
            ],
            'invalid second level' => [
                'book/{id}/author', 'book/1/authors'
            ],
            'check relashionship\'s parameter' => [
                'book/{id}', 'book/'
            ],
            'test bad request' => [
                'book-bad/{id}', 'book'
            ],
            'test case sensitivity' => [
                'BOOK/{id}', 'book/4'
            ]
        ];
    }

    /**
     * @covers Phramework\URIStrategy\URITemplate::test
     * @dataProvider testSuccessProvider
     */
    public function testTestSuccess($URITemplate, $URI, $expected)
    {
        $value = $this->object->test($URITemplate, $URI);

        //must be an array
        $this->assertIsArray($value);

        //must be a subset of $expected
        $this->assertEquals($expected, $value[0]);
//        $this->assertArraySubset($expected, $value[0], true);
    }

    /**
     * @covers Phramework\URIStrategy\URITemplate::test
     * @dataProvider testFailureProvider
     */
    public function testTestFailure($URITemplate, $URI)
    {
        $value = $this->object->test($URITemplate, $URI);

        $this->assertFalse($value);
    }

    /**
     * @covers Phramework\URIStrategy\URITemplate::URI
     */
    public function testURI()
    {
        list($URI, $parameters) = $this->object->URI();

        //check types
        $this->assertIsString($URI);

        $this->assertIsArray($parameters);
        $this->assertCount(2, $parameters);
        //from current $_SERVER values
        $this->assertEquals(['spam' => 'true', 'ok' => 'false'], $parameters);

        $this->markTestIncomplete();
    }

    /**
     * @covers Phramework\URIStrategy\URITemplate::invoke
     * @todo   Implement testInvoke().
     */
    public function testInvoke()
    {
        $this->markTestIncomplete();
    }
}