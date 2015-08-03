<?php

    namespace Tests\ObjectivePHP\Primitives;

    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\Numeric\Numeric;
    use ObjectivePHP\Primitives\String\String;

    class NumericTest extends TestCase
    {

        /**
         * @dataProvider dataProviderForTestSetValue
         */
        public function testSetValue($value, $expected)
        {
            $numeric = new Numeric($value);
            $this->assertEquals($expected, $numeric->getInternalValue());
        }

        public function dataProviderForTestSetValue()
        {
            return
                [
                    [200, 200],
                    ['0200', 200],
                    ['hey', 0],
                    [200.00, 200.00],
                    [0200, 128],
                    [new Numeric(13), 13],
                ];
        }

        /**
         * @dataProvider dataProviderTestSetValueFails
         */
        public function testSetValueFails($data)
        {
            $this
                ->expectsException(function () use ($data)
                {
                    return new Numeric($data);
                }, Exception::class, null, Exception::INVALID_PARAMETER);;
        }

        public function dataProviderTestSetValueFails()
        {
            return
                [
                    [['test']],
                    [new \stdClass],
                    [tmpfile()],
                    [PHP_INT_MAX * 2],
                ];
        }

        public function testInvokeReturnValue()
        {
            $numeric = new Numeric($value = rand());
            $this->assertEquals($value, $numeric());
        }

        public function testMirroring()
        {
            $numeric = new Numeric(1);
            $numeric->opposite();
            $this->assertEquals(-1, $numeric->getInternalValue());
            $this->assertEquals(1, $numeric->opposite()->getInternalValue());
        }

        public function testOddEven()
        {
            $numeric = new Numeric(2);
            $this->assertTrue($numeric->isOdd());
            $this->assertFalse($numeric->isEven());
        }

        public function testIsBetween()
        {
            $numeric = new Numeric(2);
            $this->assertTrue($numeric->isBetween(2, 3));
            $this->assertTrue($numeric->isBetween(1, 2));
            $this->assertFalse($numeric->isBetween(3, 4));
            $this->assertFalse($numeric->isBetween(2, 3, false));
            $this->assertFalse($numeric->isBetween(1, 2, false));
        }

        public function testLength()
        {
            $this->assertEquals(3, (new Numeric(123))->length());
        }

        public function testInHaystack()
        {
            $numeric = new Numeric(2);
            $this->assertTrue($numeric->isIn([2, 3]));
            $this->assertFalse($numeric->isIn([3]));
            $this->assertTrue($numeric->isIn(new \ArrayObject([2])));
            $this->expectsException(function () use ($numeric)
            {
                return $numeric->isIn('the wild');
            }, Exception::class, null, Exception::INVALID_PARAMETER);
        }

        public function testIsStringable()
        {
            $numeric = new Numeric(1200);
            $string = $numeric->toString();
            $this->assertInstanceOf(String::class, $string);
            $this->assertEquals('1200', $numeric->__toString());
        }

        public function testIsCanBeConvertedToChars()
        {
            $numeric = new Numeric(1200);
            $this->assertEquals('ATE', $numeric->char());
        }

        public function testIsInvokable()
        {
            $numeric = new Numeric(1);
            $this->assertEquals(1, $numeric());
        }

        public function testIsJsonSerializable()
        {
            $numeric = new Numeric(200);
            $this->assertJson(json_encode(200), $numeric->jsonSerialize());
        }

        public function testUp()
        {
            $numeric = new Numeric;
            $this
                ->assertEquals(1, $numeric->increment()->getInternalValue());
        }

        public function testDown()
        {
            $numeric = new Numeric;
            $this
                ->assertEquals(-1, $numeric->decrement()->getInternalValue());
        }

        public function testAdd()
        {
            $numeric = new Numeric;
            $this
                ->assertEquals(3, $numeric->add(3)->getInternalValue());
        }

        public function testSub()
        {
            $numeric = new Numeric;
            $this->assertEquals(-3, $numeric->subtract(3)->getInternalValue());
        }

        public function testDivide()
        {
            $numeric = new Numeric;
            $this->assertEquals(0, $numeric->divideBy(3)->getInternalValue());
            $this->assertEquals(10 / 3, $numeric->setInternalValue(10)->divideBy(3)->getInternalValue());
            $this->expectsException(function () use ($numeric)
            {
                return $numeric->setInternalValue(3)->divideBy(0)->getInternalValue();
            }, Exception::class, null, Exception::INVALID_PARAMETER);
        }

        public function testMultiply()
        {
            $numeric = new Numeric;
            $this->assertEquals(0, $numeric->multiplyBy(3)->getInternalValue());
            $this->assertEquals(25, $numeric->setInternalValue(10)->multiplyBy(2.5)->getInternalValue());
        }


        public function testNativeStringConversion()
        {
            $numeric = new Numeric(4.5);
            $this->assertEquals("4.5", (string) $numeric);
        }

        public function testStringObjectConversion()
        {
            $numeric = new Numeric(4.5);
            $this->isInstanceOf(String::class, $numeric->toString());
        }

        public function testType()
        {
            $numeric = new Numeric(4.5);
            $this->assertEquals('numeric', $numeric->getType());
        }

        public function testFormat()
        {
            $numeric = new Numeric(1234.6);

            $this->isInstanceOf(String::class, $formatted = $numeric->format());
            $this->assertEquals('1,234.60', $formatted->getInternalValue());

            $numeric = new Numeric(1234.6);

            $this->isInstanceOf(String::class, $formatted = $numeric->format(1, ',', ' '));
            $this->assertEquals('1 234,6', $formatted->getInternalValue());
        }

        public function testCast()
        {
            $value = '123';
            $castedNumeric = Numeric::cast($value);
            $this->assertInstanceOf(Numeric::class, $castedNumeric);
            $this->assertEquals($value, $castedNumeric->getInternalValue());
            $this->assertSame($castedNumeric, Numeric::cast($castedNumeric));
        }
    }