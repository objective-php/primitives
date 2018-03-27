<?php

namespace Tests\ObjectivePHP\Primitives;

use ObjectivePHP\Primitives\AbstractPrimitive;
use ObjectivePHP\Primitives\PrimitiveInterface;
use PHPUnit\Framework\TestCase;

class AbstractPrimitiveTest extends TestCase
{
    /**
     * @var AbstractPrimitive
     */
    protected $abstractPrimitive;

    public function setUp()
    {
        $this->abstractPrimitive = new class extends AbstractPrimitive {

            /**
             * Set the primitive object initial value
             *
             * @param mixed $value
             */
            public function setInternalValue($value)
            {
                $this->value = $value;
            }

            /**
             * Convert a value to Primitive
             *
             * @param mixed $value
             *
             * @return PrimitiveInterface
             */
            public static function cast($value)
            {
                return (new static())->setInternalValue($value);
            }

            /**
             * Specify data which should be serialized to JSON
             *
             * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
             * @return mixed data which can be serialized by <b>json_encode</b>,
             * which is a value of any type other than a resource.
             * @since 5.4.0
             */
            public function jsonSerialize()
            {
                return $value;
            }
        };
    }

    public function testGetSetGenericImplementations()
    {
        $abstractPrimitive = $this->abstractPrimitive;
        $abstractPrimitive->setInternalValue('test value');

        $this->assertEquals('test value', $abstractPrimitive->getInternalValue());
    }

    public function testPrimitiveCopyFunction()
    {
        $abstractPrimitive = $this->abstractPrimitive;
        $abstractPrimitive->setInternalValue('test value');
        $this->assertNotSame($abstractPrimitive, $clone = $abstractPrimitive->copy());
        $this->assertEquals($abstractPrimitive->getInternalValue(), $clone->getInternalValue());
    }
}
