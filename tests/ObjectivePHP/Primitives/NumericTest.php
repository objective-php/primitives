<?php

namespace ObjectivePHP\Primitives\tests\units;

use mageekguy\atoum;
use ObjectivePHP\Primitives\Collection as PrimitiveCollection;
use ObjectivePHP\Primitives\Exception;
use ObjectivePHP\Primitives\Numeric as TestedClass;
use ObjectivePHP\Primitives\String;

class Numeric extends atoum\test
{
    public function testSetValue($value, $expected)
    {
        $numeric = new TestedClass($value);
        $this->variable($numeric->get())->isEqualTo($expected);
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
            [new TestedClass(13)  , 13],
        ];
    }

    public function testSetValueFails($data)
    {
        $this
            ->exception(function() use($data){
                return new TestedClass($data);
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
        $numeric = new TestedClass(1);
        $this->integer($numeric())->isEqualTo(1);
    }

    public function testMirroring()
    {
        $numeric = new TestedClass(1);
        $inversed = $numeric->opposite();
        $this
            ->integer($inversed->get())
                ->isEqualTo(-1)
            ->integer($inversed->opposite()->get())
                ->isEqualTo(1)
        ;
    }

    public function testOddEven()
    {
        $numeric = new TestedClass(2);
        $this
            ->boolean($numeric->isOdd())
                ->isTrue()
            ->boolean($numeric->isEven())
                ->isFalse()
        ;
    }

    public function testIsBetween()
    {
        $numeric = new TestedClass(2);
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
            ->integer((new TestedClass(123))->length())
            ->isEqualTo(3);
    }

    public function testInHaystack()
    {
        $numeric = new TestedClass(2);
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
        $numeric = new TestedClass(10);
        $this
            ->object($numeric->split())
                ->isInstanceOf(PrimitiveCollection::class)
            ->sizeOf($numeric->split())
                ->isEqualTo(10)
            ->sizeOf($numeric->split(2))
                ->isEqualTo(5)

            ->sizeOf($numeric->set(-10)->split())
                ->isEqualTo(10)
            ->sizeOf($numeric->split(2))
                ->isEqualTo(5)

            ->array($zero = $numeric->set(0)->split()->getArrayCopy())
                ->hasSize(1)
                ->strictlyContains(0)

            ->array($numeric->set(10)->split(function(){ return 'a';  })->getArrayCopy())
                ->hasSize(10)
                ->strictlyContains('a')
        ;
    }

    public function testIsStringable()
    {
        $numeric = new TestedClass(1200);
        $this
            ->object($numeric->string())
                ->isInstanceOf(String::class)
            ->string($numeric->__toString())
                ->isIdenticalTo('1200')
        ;
    }

    public function testIsCanBeConvertedToChars()
    {
        $numeric = new TestedClass(1200);
        $this
            ->string($numeric->char())
            ->isEqualTo('ATE')
        ;
    }

    public function testIsInvokable()
    {
        $numeric = new TestedClass(1);
        $this
            ->integer($numeric())
            ->isEqualTo(1)
        ;
    }

    public function testIsJsonSerializable()
    {
        $numeric = new TestedClass(200);
        $this
            ->variable($numeric->jsonSerialize())
            ->isEqualTo(json_encode(200))
        ;
    }

    public function testUp()
    {
        $numeric = new TestedClass;
        $this
            ->integer($numeric->increment()->get())
            ->isEqualTo(1)
        ;
    }

    public function testDown()
    {
        $numeric = new TestedClass;
        $this
            ->integer($numeric->decrement()->get())
            ->isEqualTo(-1)
        ;
    }

    public function testAdd()
    {
        $numeric = new TestedClass;
        $this
            ->integer($numeric->add(3)->get())
            ->isEqualTo(3)
        ;
    }

    public function testSub()
    {
        $numeric = new TestedClass;
        $this
            ->integer($numeric->subtract(3)->get())
            ->isEqualTo(-3)
        ;
    }

    public function testDivide()
    {
        $numeric = new TestedClass;
        $this
            ->variable($numeric->divideBy(3)->get())
                ->isEqualTo(0)
            ->variable($numeric->set(10)->divideBy(3)->get())
                ->isEqualTo(10/3)
            ->exception(function() use($numeric) {
                return $numeric->set(3)->divideBy(0)->get();
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER)
        ;
    }

    public function testMultiply()
    {
        $numeric = new TestedClass;
        $this
            ->variable($numeric->multiplyBy(3)->get())
                ->isEqualTo(0)
            ->variable($numeric->set(10)->multiplyBy(2.5)->get())
                ->isEqualTo(25)
        ;
    }


    public function testNativeStringConversion()
    {
        $numeric = new TestedClass(4.5);
        $this->string((string) $numeric)->isEqualTo("4,5");
    }

    public function testStringObjectConversion()
    {
        $numeric = new TestedClass(4.5);
        $this->object($numeric->string())->isInstanceOf(String::class);
    }

    public function testType()
    {
        $numeric = new TestedClass(4.5);
        $this->string($numeric->getType())->isEqualTo('numeric');
    }

}