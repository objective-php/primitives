<?php

namespace ObjectivePHP\Primitives\tests\units;

use mageekguy\atoum;
use ObjectivePHP\Primitives\Collection as PrimitiveCollection;
use ObjectivePHP\Primitives\Exception;
use ObjectivePHP\Primitives\Numeric;
use ObjectivePHP\Primitives\String;

class NumericTest extends atoum\test
{

    public function __construct(atoum\adapter $adapter = null, atoum\annotations\extractor $annotationExtractor = null, atoum\asserter\generator $asserterGenerator = null, atoum\test\assertion\manager $assertionManager = null, \closure $reflectionClassFactory = null)
    {
        $this->setTestedClassName(Numeric::class);
        parent::__construct($adapter, $annotationExtractor, $asserterGenerator, $assertionManager, $reflectionClassFactory);
    }

    public function testSetValue($value, $expected)
    {
        $numeric = new Numeric($value);
        $this->variable($numeric->getInternalValue())->isEqualTo($expected);
    }

    protected function testSetValueDataProvider()
    {
        return
        [
            [200   , 200],
            ['0200', 200],
            ['hey' , 0],
            [200.00, 200.00],
            [0200  , 128],
            [new Numeric(13)  , 13],
        ];
    }

    public function testSetValueFails($data)
    {
        $this
            ->exception(function() use($data){
                return new Numeric($data);
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER);
        ;
    }
    protected function testSetValueFailsDataProvider()
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
        $numeric = new Numeric(1);
        $this->integer($numeric())->isEqualTo(1);
    }

    public function testMirroring()
    {
        $numeric = new Numeric(1);
        $numeric->opposite();
        $this
            ->integer($numeric->getInternalValue())
                ->isEqualTo(-1)
            ->integer($numeric->opposite()->getInternalValue())
                ->isEqualTo(1)
        ;
    }

    public function testOddEven()
    {
        $numeric = new Numeric(2);
        $this
            ->boolean($numeric->isOdd())
                ->isTrue()
            ->boolean($numeric->isEven())
                ->isFalse()
        ;
    }

    public function testIsBetween()
    {
        $numeric = new Numeric(2);
        $this
            ->boolean($numeric->isBetween(2, 3))
                ->isTrue()
            ->boolean($numeric->isBetween(1, 2))
                ->isTrue()
            ->boolean($numeric->isBetween(3, 4))
                ->isFalse()
            ->boolean($numeric->isBetween(2, 3, false))
                ->isFalse()
            ->boolean($numeric->isBetween(1, 2, false))
                ->isFalse()
        ;
    }

    public function testLength()
    {
        $this
            ->integer((new Numeric(123))->length())
            ->isEqualTo(3);
    }

    public function testInHaystack()
    {
        $numeric = new Numeric(2);
        $this
            ->boolean($numeric->isIn([2, 3]))
                ->isTrue()
            ->boolean($numeric->isIn([3]))
                ->isFalse()
            ->boolean($numeric->isIn(new \ArrayObject([2])))
                ->isTrue()

            ->exception(function() use ($numeric){
                return $numeric->isIn('the wild');
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER);
    }

    public function testIsSplittable()
    {
        $numeric = new Numeric(10);
        $this
            ->object($numeric->split())
                ->isInstanceOf(PrimitiveCollection::class)
            ->sizeOf($numeric->split())
                ->isEqualTo(10)
            ->sizeOf($numeric->split(2))
                ->isEqualTo(5)

            ->sizeOf($numeric->setInternalValue(-10)->split())
                ->isEqualTo(10)
            ->sizeOf($numeric->split(2))
                ->isEqualTo(5)

            ->array($zero = $numeric->setInternalValue(0)->split()->getArrayCopy())
                ->hasSize(1)
                ->strictlyContains(0)

            ->array($numeric->setInternalValue(10)->split(function(){ return 'a';  })->getArrayCopy())
                ->hasSize(10)
                ->strictlyContains('a')
        ;
    }

    public function testIsStringable()
    {
        $numeric = new Numeric(1200);
        $this
            ->object($numeric->toString())
                ->isInstanceOf(String::class)
            ->string($numeric->__toString())
                ->isIdenticalTo('1200')
        ;
    }

    public function testIsCanBeConvertedToChars()
    {
        $numeric = new Numeric(1200);
        $this
            ->string($numeric->char())
            ->isEqualTo('ATE')
        ;
    }

    public function testIsInvokable()
    {
        $numeric = new Numeric(1);
        $this
            ->integer($numeric())
            ->isEqualTo(1)
        ;
    }

    public function testIsJsonSerializable()
    {
        $numeric = new Numeric(200);
        $this
            ->variable($numeric->jsonSerialize())
            ->isEqualTo(json_encode(200))
        ;
    }

    public function testUp()
    {
        $numeric = new Numeric;
        $this
            ->integer($numeric->increment()->getInternalValue())
            ->isEqualTo(1)
        ;
    }

    public function testDown()
    {
        $numeric = new Numeric;
        $this
            ->integer($numeric->decrement()->getInternalValue())
            ->isEqualTo(-1)
        ;
    }

    public function testAdd()
    {
        $numeric = new Numeric;
        $this
            ->integer($numeric->add(3)->getInternalValue())
            ->isEqualTo(3)
        ;
    }

    public function testSub()
    {
        $numeric = new Numeric;
        $this
            ->integer($numeric->subtract(3)->getInternalValue())
            ->isEqualTo(-3)
        ;
    }

    public function testDivide()
    {
        $numeric = new Numeric;
        $this
            ->variable($numeric->divideBy(3)->getInternalValue())
                ->isEqualTo(0)
            ->variable($numeric->setInternalValue(10)->divideBy(3)->getInternalValue())
                ->isEqualTo(10/3)
            ->exception(function() use($numeric) {
                return $numeric->setInternalValue(3)->divideBy(0)->getInternalValue();
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER)
        ;
    }

    public function testMultiply()
    {
        $numeric = new Numeric;
        $this
            ->variable($numeric->multiplyBy(3)->getInternalValue())
                ->isEqualTo(0)
            ->variable($numeric->setInternalValue(10)->multiplyBy(2.5)->getInternalValue())
                ->isEqualTo(25)
        ;
    }


    public function testNativeStringConversion()
    {
        $numeric = new Numeric(4.5);
        $this->string((string) $numeric)->isEqualTo("4.5");
    }

    public function testStringObjectConversion()
    {
        $numeric = new Numeric(4.5);
        $this->object($numeric->toString())->isInstanceOf(String::class);
    }

    public function testType()
    {
        $numeric = new Numeric(4.5);
        $this->string($numeric->getType())->isEqualTo('numeric');
    }

    public function testFormat()
    {
        $numeric = new Numeric(1234.6);

        $this->object($formatted = $numeric->format())->isInstanceOf(String::class)
                ->string($formatted->getInternalValue())->isEqualTo('1,234.60');

        $numeric = new Numeric(1234.6);

        $this->object($formatted = $numeric->format(1, ',', ' '))->isInstanceOf(String::class)
                ->string($formatted->getInternalValue())->isEqualTo('1 234,6');

    }
}