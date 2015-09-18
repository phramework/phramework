<?php

namespace Phramework\URIStrategy;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-09-18 at 02:24:31.
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
        'book/', 'BookController', 'GET', \Phramework\API::METHOD_ANY,
        'author/', 'AuthorController', 'POST', \Phramework\API::METHOD_ANY
    ];
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $this->object = new URITemplate($this->testTemplates);
        
        $_SERVER['QUERY_STRING'] = 'spam=true&ok=false';
        $_SERVER['REQUEST_URI'] = '/api/book/1?spam=true&ok=false';
        
        $_SERVER['SCRIPT_NAME'] = '/api/index.php';
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers Phramework\URIStrategy\URITemplate::test
     */
    public function testTest()
    {
        /**
         * Define assertions tests
         * format is [URI template, (clean) URI, expected]
         */
        $assertions = [
            ['book/', 'book', []],
            ['book/', 'books', false],
            //check parameter
            ['book/{id}', 'book/1', ['id' => '1']],
            //check alphanumeric parameter
            ['book/{id}', 'book/abcdefABCDE0123', ['id' => 'abcdefABCDE0123']],
            //check relashionship
            ['book/{id}/author', 'book/1/author', ['id' => '1']],
            //check relashionship
            ['book/{id}/author', 'book/1/authors', false],
            //check two level
            ['book/author', 'book/author', []],
            //check two level with alphanumeric parameter
            ['book/author/{id}', 'book/author/500abc', ['id' => '500abc']],
            //check relashionship's parameter
            ['book/{id}', 'book/', false],
            //check relashionship's multiple parameters
            [
                'book/{id}/author/{author_id}',
                'book/3/author/2',
                ['id' => '3', 'author_id' => '2']
            ],
            //test bad request
            ['book-bad/{id}', 'book', false],
            //test case sensitivity
            ['BOOK/{id}', 'book/4', false]
        ];
        
        foreach ($assertions as $k => $a) {
            $value = $this->object->test($a[0], $a[1]);
            
            //debug print_r([$k, $a, $value]);
            
            $expected = $a[2];
            
            if ($expected === false) {
                //must be false
                $this->assertFalse($value);
            }else{
                //must be an array
                $this->assertInternalType('array', $value);
                
                //must be a subset
                $this->assertArraySubset($expected, $value[0], true);
            }
        }
    }

    /**
     * @covers Phramework\URIStrategy\URITemplate::URI
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
    }

    /**
     * @covers Phramework\URIStrategy\URITemplate::invoke
     * @todo   Implement testInvoke().
     */
    public function testInvoke()
    {
        
    }

}
