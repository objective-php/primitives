<?php

namespace ObjectivePHP\Primitives;

/**
 * Class AbstractPrimitive
 *
 * @package ObjectivePHP\Primitives
 */
abstract class AbstractPrimitive implements PrimitiveInterface
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * {@inheritdoc}
     */
    public function getInternalValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function copy()
    {
        return clone $this;
    }
}
