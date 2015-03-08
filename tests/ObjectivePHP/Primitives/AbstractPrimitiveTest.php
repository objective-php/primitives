<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 08/03/15
 * Time: 22:12
 */

namespace ObjectivePHP\Primitives\tests\units;

use mageekguy\atoum;
use ObjectivePHP\Primitives\AbstractPrimitive as ActualAbstractPrimitive;

class AbstractPrimitive extends atoum\test
{

    /**
     * @var ActualAbstractPrimitive
     */
    protected $abstractPrimitive;

    public function testGetSetGenericImplementations()
    {
        $abstractPrimitive = new \mock\ObjectivePHP\Primitives\AbstractPrimitive;
        $abstractPrimitive->set('test value');

        $this->string($abstractPrimitive->get())->isEqualTo('test value');
    }

    public function testApplyGenericImplementation()
    {

        $abstractPrimitive = new \mock\ObjectivePHP\Primitives\AbstractPrimitive;
        $abstractPrimitive->set('test value');

        $abstractPrimitive->apply(function ($value) { return mb_strtoupper($value);});

        $this->string($abstractPrimitive->get())->isIdenticalTo('TEST VALUE');
    }

    public function testTypeAccessor()
    {

        $abstractPrimitive = new \mock\ObjectivePHP\Primitives\AbstractPrimitive;
        $this->string($abstractPrimitive->getType())->isEqualTo('ABSTRACT');

    }
}