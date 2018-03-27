<?php

namespace ObjectivePHP\Primitives\Collection;

use ObjectivePHP\Primitives\AbstractPrimitive;

/**
 * Class AbstractCollection
 *
 * @package src\Collection
 */
abstract class AbstractCollection extends AbstractPrimitive implements CollectionInterface
{
    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function clear()
    {
        $this->value = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function fromArray(array $array)
    {
        $this->setInternalValue($array);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $array = $this->getInternalValue();

        foreach ($array as &$value) {
            if ($value instanceof CollectionInterface) {
                $value = $value->toArray();
            }
        }

        return $array;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
