<?php
    namespace Tests\ObjectivePHP\Primitives;

    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\AbstractPrimitive;

    class AbstractPrimitiveTest extends TestCase
    {

        /**
         * @var \PHPUnit_Framework_MockObject_MockBuilder
         */
        protected $abstractPrimitive;

        public function setUp()
        {
            $this->abstractPrimitive = $this->getMockBuilder(AbstractPrimitive::class);
        }

        public function testGetSetGenericImplementations()
        {
            $abstractPrimitive = $this->abstractPrimitive->setMethods(['cast'])->getMock();
            $abstractPrimitive->setInternalValue('test value');

            $this->assertEquals('test value', $abstractPrimitive->getInternalValue());
        }

        public function testApplyGenericImplementation()
        {

            $abstractPrimitive = $this->abstractPrimitive->setMethods(['cast'])->getMock();
            $abstractPrimitive->setInternalValue('test value');

            $abstractPrimitive->apply(function ($value)
            {
                return mb_strtoupper($value);
            });

            $this->assertEquals('TEST VALUE', $abstractPrimitive->getInternalValue());
        }

        public function testTypeAccessor()
        {

            $abstractPrimitive = $this->abstractPrimitive->setMethods(['cast'])->getMock();
            $this->assertEquals('ABSTRACT', $abstractPrimitive->getType());
        }

        public function testPrimitiveCopyFunction()
        {
            $abstractPrimitive = $this->abstractPrimitive->setMethods(['cast'])->getMock();
            $abstractPrimitive->setInternalValue('test value');
            $this->assertNotSame($abstractPrimitive, $clone = $abstractPrimitive->copy());
            $this->assertEquals($abstractPrimitive->getInternalValue(), $clone->getInternalValue());
        }

        public function testCanIdentifyPrimitiveClasses()
        {
            
        }
    }