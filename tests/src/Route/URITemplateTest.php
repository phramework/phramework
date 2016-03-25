<?php

namespace Phramework\Route;

use \Phramework\Phramework;

/**
 * @todo check request method
 * @coversDefaultClass Phramework\Route\URITemplate
 */
class URITemplateTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
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
            'check parameter (int class)' =>    [
                'book/{id|int}', 'book/1', ['id' => '1']
            ],
            'check parameter' =>    [
                'book/{id}',
                'book/a7a5dea9-e64f-4431-a867-4611025ef768',
                ['id' => 'a7a5dea9-e64f-4431-a867-4611025ef768']
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
            'typo' => [
                'book', 'books'
            ],
            'invalid second level' => [
                'book/{id}/author', 'book/1/authors'
            ],
            'string parameter for integer class' => [
                'book/{id|int}', 'book/myBook'
            ],
            'missing required parameter' => [
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
     * @covers ::test
     * @dataProvider testSuccessProvider
     */
    public function testTestSuccess($URITemplate, $URI, $expected)
    {
        $value = $this->object->test($URITemplate, $URI);

        //must be an array
        $this->assertInternalType('array', $value);

        //must be a subset of $expected
        $this->assertArraySubset($expected, $value[0], true);
    }

    /**
     * @covers ::test
     * @dataProvider testFailureProvider
     */
    public function testTestFailure($URITemplate, $URI)
    {
        $value = $this->object->test($URITemplate, $URI);

        $this->assertFalse($value);
    }

    /**
     * @covers ::URI
     */
    public function testURI()
    {
        list($URI, $parameters) = $this->object->URI();

        //check types
        $this->assertInternalType('string', $URI);
        $this->assertInternalType('array', $parameters);

        $this->assertInternalType('array', $parameters);
        $this->assertCount(2, $parameters);
        //from current $_SERVER values
        $this->assertArraySubset(['spam' => 'true', 'ok' => 'false'], $parameters, false);

        $this->markTestIncomplete();
    }

    /**
     * @covers ::invoke
     * @todo   Implement testInvoke().
     */
    public function testInvoke()
    {
        $this->markTestIncomplete();
    }
}
