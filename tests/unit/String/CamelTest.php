<?php

namespace Tests\ObjectivePHP\Primitives\String;

use ObjectivePHP\Primitives\String\Camel;
use PHPUnit\Framework\TestCase;

/**
 * Class CamelTest
 *
 * @package Tests\Primitives\String
 */
class CamelTest extends TestCase
{
    /**
     * @param $snake
     * @param $camel
     *
     * @dataProvider getDataForTestCamelization
     */
    public function testCamelization($snake, $camel, $flag, $delimiter = '_')
    {
        $this->assertEquals($camel, Camel::case($snake, $flag, $delimiter));
    }

    public function getDataForTestCamelization()
    {
        return [
            ['test_string', 'TestString', null],
            ['testString', 'Teststring', null],
            ['test_string', 'TestString', Camel::UPPER],
            ['test_string', 'testString', Camel::LOWER],
            ['test.string', 'TestString', null, '.'],
            ['test.string', 'TestString', null, ['.']],
            ['longer_test.string', 'LongerTestString', null, ['.', '_']],
        ];
    }
}
