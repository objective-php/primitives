<?php

namespace Tests\ObjectivePHP\Primitives\String;

use ObjectivePHP\Primitives\String\Snake;
use PHPUnit\Framework\TestCase;

/**
 * Class SnakeTest
 *
 * @package Tests\Primitives\String
 */
class SnakeTest extends TestCase
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

    public function getDataForTestSnakization()
    {
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
