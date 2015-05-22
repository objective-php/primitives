<?php

    namespace ObjectivePHP\Primitives\Numeric;


    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Float;

    class FloatTest extends TestCase
    {
        public function testCasting()
        {
            $float = Float::cast(12);
            $this->assertInstanceOf(Float::class, $float);
        }

        public function testInternalValueIsCastedToInteger()
        {
            $float = new Float(12);
            $this->assertAttributeSame(12.0, 'value', $float);

            $float->setInternalValue(15);
            $this->assertAttributeSame(15.0, 'value', $float);
            
        }
    }