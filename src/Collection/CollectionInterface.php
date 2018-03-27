<?php

namespace ObjectivePHP\Primitives\Collection;

use ObjectivePHP\Primitives\PrimitiveInterface;

/**
 * Interface CollectionInterface
 *
 * @package ObjectivePHP\Primitives\Collection
 */
interface CollectionInterface extends PrimitiveInterface, \Iterator, \Countable
{
    /**
     * Removes all values of the collection
     */
    public function clear();

    /**
     * Returns whether the collection is empty
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Set the collection with an array
     *
     * @param array $array
     */
    public function fromArray(array $array);

    /**
     * Converts the collection to an tableau
     *
     * @return array
     */
    public function toArray(): array;
}
