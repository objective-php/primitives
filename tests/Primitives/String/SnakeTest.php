<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 04/07/2017
 * Time: 11:46
 */

namespace Tests\Primitives\String;


use ObjectivePHP\Primitives\String\Camel;
use ObjectivePHP\Primitives\String\Snake;

class SnakeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $snake
     * @param $camel
     *
     * @dataProvider getDataForTestSnakization
     */
    public function testSnakization($camel, $snake, $glue = '_')
    {
        $this->assertEquals($snake, Snake::case($camel, $glue));
    }
    
    public function getDataForTestSnakization(){
        return [
            ['TestString', 'test_string'],
            ['testString', 'test_string'],
            ['teststring', 'teststring'],
            ['TESTString', 'test_string'],
            ['OtherTESTString', 'other_test_string'],
            ['OtherTESTString', 'other.test.string', '.']
        ];
    }
}
