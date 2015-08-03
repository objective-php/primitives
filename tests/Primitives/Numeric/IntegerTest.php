<?php

    namespace ObjectivePHP\Primitives\Numeric;


    use ObjectivePHP\PHPUnit\TestCase;

    class IntegerTest extends TestCase
    {
        public function testCasting()
        {
            $integer = Integer::cast(12);

            $this->assertInstanceOf(Integer::class, $integer);
        }

        public function testInternalValueIsCastedToInteger()
        {
            $integer = new Integer(12.3);
            $this->assertAttributeSame(12, 'value', $integer);

            $integer->setInternalValue(15.6);
            $this->assertAttributeSame(15, 'value', $integer);

        }
    }