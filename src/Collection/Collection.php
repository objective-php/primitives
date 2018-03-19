<?php

namespace ObjectivePHP\Primitives\Collection;

use ObjectivePHP\Primitives\Exception\BreakException;
use ObjectivePHP\Primitives\Exception\CollectionException;
use ObjectivePHP\Primitives\Exception\UnderflowException;
use ObjectivePHP\Primitives\Exception\UnsupportedTypeException;
use ObjectivePHP\Primitives\String\Str;

/**
 * Class Collection
 *
 * @package ObjectivePHP\Primitives
 */
class Collection extends AbstractCollection implements \ArrayAccess
{
    /**
     * @var string
     */
    protected $type;

    /**
     * Collection constructor.
     *
     * @param \Traversable|array $value      Initialize the collection with a traversable or an array
     * @param string|null        $restrictTo Restricts the values of the collection to a given type
     */
    public function __construct($value = [], string $restrictTo = null)
    {
        if (!is_null($restrictTo)) {
            $this->restrictTo($restrictTo);
        }

        $this->setInternalValue($value);
    }

    /**
     * Restricts the values of the collection to a given type
     *
     * @param string $type
     *
     * @return $this
     */
    public function restrictTo(string $type)
    {
        if (count($this) > 0) {
            throw new CollectionException('Type restriction could not be applied to a non empty collection');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Returns the type which the values of the collection were restricted
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     *
     * Value could be an ArrayObject, an Iterator, an array or a a scalar
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setInternalValue($value)
    {
        if ($value instanceof \ArrayObject) {
            $value = $value->getArrayCopy();
        }

        if ($value instanceof \Iterator) {
            $value = iterator_to_array($value);
        }

        // force null values conversion to empty arrays
        if (is_null($value)) {
            $value = [];
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $this->clear();

        foreach ($value as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    /**
     * Define a key and associate a value to it
     *
     * Associates a key with a value, overwriting a previous association if one exists.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->checkRestriction($value);

        if (!is_null($key)) {
            $this->value[$key] = $value;
        } else {
            $this->value[] = $value;
        }

        return $this;
    }

    /**
     * Creates a new collection using values from the current instance and another map
     *
     * This uses the same rules as "array + array" (union) operation in native PHP
     *
     * @param mixed $value
     *
     * @return Collection
     *
     * @deprecated Use union instead
     */
    public function add($value)
    {
        return $this->union($value);
    }

    /**
     * Adds values to the end of the sequence
     *
     * Note: multiple values will be added in the same order that they are passed.
     *
     * @param mixed[] $values
     *
     * @return $this
     */
    public function append(...$values)
    {
        foreach ($values as $value) {
            $this[] = $value;
        }

        return $this;
    }

    /**
     * Updates all values by applying a callback function to each value
     *
     * Callback has the following form:
     *
     * `mixed callback (mixed $value, mixed key)`
     *
     * With:
     * - value: the value of the current iteration
     * - key: the key of the current iteration
     *
     * The value returns by the callback must respect type restriction if is set
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function apply(callable $callback)
    {
        $this->setInternalValue($this->map($callback)->getInternalValue());

        return $this;
    }

    /**
     * @param Collection $collection
     *
     * @return Collection
     */
    public function diff(Collection $collection): Collection
    {
        throw new CollectionException('Method ' . __METHOD__ . ' is not implemented');
    }

    /**
     * Creates a new collection using a callable to determine which values to include
     *
     * Optional callable which returns TRUE if the value should be included, FALSE otherwise.
     *
     * If a callback is not provided, only values which are TRUE (see converting to boolean) will be included.
     *
     * Callback has the following form:
     *
     * `bool callback (mixed $value, mixed $key)`
     *
     * With:
     * - value: the value of the current iteration
     * - key: the key of the current iteration
     *
     * @param callable|null $callback
     *
     * @return Collection
     */
    public function filter(callable $callback = null): Collection
    {
        if (is_null($callback)) {
            return new static(array_filter($this->getInternalValue()), $this->type);
        }

        return new static(array_filter($this->getInternalValue(), $callback, ARRAY_FILTER_USE_BOTH), $this->type);
    }

    /**
     * Return the first element of the collection
     *
     * The first element is the first having been added to the collection,
     * not necessarily the one with the lowest index (for numerical indices)
     *
     * @return mixed
     *
     * @throws UnderflowException
     */
    public function first()
    {
        if ($this->count() == 0) {
            throw new UnderflowException('Unexpected empty state');
        }

        $values = $this->toArray();
        reset($values);
        $lastKey = key($values);

        return $this->get($lastKey);
    }

    /**
     *  Returns the value for a given key, or an optional default value if the key could not be found.
     *
     * @param mixed      $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->hasKey($key) ? $this->getInternalValue()[$key] : $default;
    }

    /**
     * Determines whether the collection contains a given key
     *
     * @param mixed $key
     *
     * @return bool
     *
     * @deprecated Use hasKey instead
     */
    public function has($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Determines whether the collection contains a given key
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function hasKey($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Is the given key missing in the Collection?
     *
     * @param mixed $key
     *
     * @return bool
     *
     * @deprecated Use lacksKey instead
     */
    public function lacks($key): bool
    {
        return !$this->has($key);
    }

    /**
     * Is the given key missing in the Collection?
     *
     * @param mixed $key
     *
     * @return bool
     *
     * @deprecated Use lacksKey instead
     */
    public function lacksKey($key): bool
    {
        return !$this->hasKey($key);
    }

    /**
     * Determines whether the collection contains a given value
     *
     * Values will be compared by value and by type if strict is set to true.
     *
     * String comparison is done in a case-sensitive manner.
     *
     * @param mixed $value
     * @param bool  $strict
     *
     * @return bool
     */
    public function contains($value, bool $strict = false): bool
    {
        return (is_bool($this->search($value, $strict)) ? false : true);
    }

    /**
     * Removes and returns a value by key, or return an optional default value if the key could not be found.
     *
     * @param mixed      $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function delete($key, $default = null)
    {
        $value = $this->get($key, $default);

        unset($this->value[$key]);

        return $value;
    }

    /**
     * Iterates collection. Value is passed by reference in the callback.
     *
     * Callback has the following form:
     *
     * `mixed callback (mixed $value, mixed key)`
     *
     * With:
     * - value: the value of the current iteration
     * - key: the key of the current iteration
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function each(callable $callable)
    {
        foreach ($this->value as $key => &$val) {
            try {
                $callable($val, $key);
            } catch (BreakException $e) {
                // catching a BreakException means that $callable
                // requested to end the loop
                break;
            }
        }

        return $this;
    }

    /**
     * Creates a new collection with the keys and values of the current collection inverted
     *
     * @return Collection
     */
    public function flip()
    {
        // get null valued data
        $unvaluedEntries = $this->filter(function ($value) {
            return !$value;
        })->keys();

        $collection = $this->filter();

        return new static(array_merge(array_flip($collection->toArray()), $unvaluedEntries->toArray()));
    }

    /**
     * Creates a new collection by intersecting keys with another collection
     *
     * Creates a new collection containing the pairs of the current instance whose keys are also present in the given
     * collection. In other words, returns a copy of the current instance with all keys removed that are not also in
     * the other collection.
     *
     * Values from the current instance will be kept.
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    public function intersect(Collection $collection): Collection
    {
        throw new CollectionException('Method ' . __METHOD__ . ' is not implemented');
    }

    /**
     * Joins all values together as a string
     *
     * @param string|null $glue
     *
     * @return Str
     */
    public function join(string $glue = ' '): Str
    {
        return new Str(implode($glue, $this->values()->toArray()));
    }

    /**
     * Return a new Collection with current keys as values
     *
     * @return Collection
     */
    public function keys(): Collection
    {
        return new static(array_keys($this->getInternalValue()));
    }

    /**
     * Sorts the map in-place by key, using an optional comparator function.
     *
     * Comparator has the following form:
     *
     * `bool callback (mixed $a, mixed $b)`
     *
     * With:
     * - a: the key of the current iteration
     * - b: the value of the current iteration.
     *
     * The comparison function must return an integer less than, equal to, or greater than 0 if the first argument is
     * considered, respectively, less than, equal to, or greater than the second.
     *
     * Attention: returning non-integer values from the comparison function, such as float, will result in an internal
     * cast to integer of the callback's return value. So values such as 0.99 and 0.1 will both be cast to an integer
     * value of 0, which will compare such values as equal.
     *
     * @param callable|null $comparator
     */
    public function ksort(callable $comparator = null)
    {
        throw new CollectionException('Method ' . __METHOD__ . ' is not implemented');
    }

    /**
     * Returns a copy sorted by key,  using an optional comparator function.
     *
     * Comparator has the following form:
     *
     * `bool callback (mixed $a, mixed $b)`
     *
     * With:
     * - a: the key of the current iteration
     * - b: the value of the current iteration.
     *
     * The comparison function must return an integer less than, equal to, or greater than 0 if the first argument is
     * considered, respectively, less than, equal to, or greater than the second.
     *
     * Attention: returning non-integer values from the comparison function, such as float, will result in an internal
     * cast to integer of the callback's return value. So values such as 0.99 and 0.1 will both be cast to an integer
     * value of 0, which will compare such values as equal.
     *
     * @param callable|null $comparator
     *
     * @return Collection
     */
    public function ksorted(callable $comparator = null): Collection
    {
        throw new CollectionException('Method ' . __METHOD__ . ' is not implemented');
    }

    /**
     * Return the last element of the collection
     *
     * The last element is the last having been added to the collection,
     * not necessarily the one with the lowest index (for numerical indices)
     *
     * @return mixed Last appended item
     *
     * @throws UnderflowException
     */
    public function last()
    {
        if ($this->count() == 0) {
            throw new UnderflowException('Unexpected empty state');
        }

        $values = $this->toArray();
        end($values);
        $lastKey = key($values);

        return $this->get($lastKey);
    }

    /**
     * Returns a new collection which is the result of applying a callback to each value of the collection.
     *
     * The keys and values of the current instance won't be affected.
     *
     * Callback has the following form:
     *
     * `mixed callback (mixed $value, mixed key)`
     *
     * With:
     * - value: the value of the current iteration
     * - key: the key of the current iteration
     *
     * The value returns by the callback must respect type restriction if is set
     *
     * @param callable $callback
     *
     * @return Collection
     */
    public function map(callable $callback): Collection
    {
        $values = [];

        foreach ($this->getInternalValue() as $key => $value) {
            $values[$key] = $callback($value, $key);
        }

        return new static($values, $this->type);
    }

    /**
     * Returns a new collection which is the result of associating all keys of a given value with their corresponding
     * values, combined with the current instance.
     *
     * Values of the current instance will be overwritten by those provided where keys are equal.
     *
     * The current instance won't be affected.
     *
     * The values to merge must respect type restriction if is set
     *
     * @param mixed $value Value to merge
     *
     * @return Collection
     */
    public function merge($value): Collection
    {
        return new static(array_merge($this->getInternalValue(), $value), $this->type);
    }

    /**
     * Adds values to the front of the sequence
     *
     * Note: multiple values will be added in the same order that they are passed.
     *
     * @param mixed[] ...$values
     *
     * @return $this
     */
    public function prepend(...$values)
    {
        if (!empty($values)) {
            $this->checkRestriction(...$values);

            array_unshift($this->value, ...$values);
        }

        return $this;
    }

    /**
     * Reduces the sequence to a single value using a callback function
     *
     * callback has the following form:
     *
     * `mixed callback (mixed $carry, mixed $value, mixed key)`
     *
     * With:
     * - carry: the return value of the previous callback, or initial if it's the first iteration
     * - value: the value of the current iteration
     * - key: the key of the current iteration
     *
     * @param callable   $callback
     * @param mixed|null $initial The initial value of the carry value.
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        throw new CollectionException('Method ' . __METHOD__ . ' is not implemented');
    }

    /**
     * Create a copy of the collection with the value of a key renamed by a new one
     *
     * @param mixed $from
     * @param mixed $to
     *
     * @return Collection
     *
     * @throws CollectionException
     */
    public function rename($from, $to)
    {
        $arrayCopy = $this->toArray();

        if (!array_key_exists($from, $arrayCopy)) {
            throw new CollectionException(sprintf('The key "%s" was not found.', $from));
        }

        $keys = array_keys($arrayCopy);
        $keys[array_search($from, $keys)] = $to;

        return new static(array_combine($keys, $arrayCopy), $this->type);
    }

    /**
     * Reverses the collection in-place
     *
     * @return $this
     */
    public function reverse()
    {
        $this->setInternalValue(array_reverse($this->getInternalValue(), true));

        return $this;
    }

    /**
     * Returns a reversed copy
     *
     * The current instance is not affected.
     *
     * @return Collection
     */
    public function reversed(): Collection
    {
        return new static(array_reverse($this->getInternalValue(), true), $this->type);
    }

    /**
     * Search a value in the Collection and return the associated key
     *
     * If search is not strict, strings will be compared ignoring case
     *
     * @param mixed $value
     * @param bool  $strict
     *
     * @return mixed
     */
    public function search($value, bool $strict = false)
    {
        if (!$strict) {
            /** @var Collection $values */
            $values = $this->copy();
            $values->apply(function ($value) {
                if (is_string($value)) {
                    return strtolower($value);
                }

                return $value;
            });

            $value = strtolower($value);
        } else {
            $values = $this;
        }

        return array_search($value, $values->toArray(), $strict);
    }

    /**
     * Returns the value at a given positional index
     *
     * @param int $position The zero-based positional index to return.
     *
     * @return mixed
     *
     * @throws OutOfRangeException if the position is not valid.
     */
    public function skip(int $position)
    {
        throw new CollectionException('Method ' . __METHOD__ . ' is not implemented');
    }

    /**
     * Returns a subset of the map defined by a starting index and length
     *
     * @param int $index       The index at which the range starts. If positive, the range will start at that index in
     *                         the collection. If negative, the range will start that far from the end.
     * @param int|null $length If a length is given and is positive, the resulting collection will have up to that
     *                         many values in it. If a length is given and is negative, the range will stop that many
     *                         values from the end. If the length results in an overflow, only values up to the end of
     *                         the collection will be included. If a length is not provided, the resulting collection
     *                         will contain all values between the index and the end of the collection.
     *
     * @return Collection
     */
    public function slice(int $index, int $length = null): Collection
    {
        throw new CollectionException('Method ' . __METHOD__ . ' is not implemented');
    }

    /**
     * Creates a new collection using values from the current instance and another map
     *
     * This uses the same rules as "array + array" (union) operation in native PHP
     *
     * @param mixed $value
     *
     * @return Collection
     */
    public function union($value)
    {
        // force data conversion to array
        $value = Collection::cast($value)->getInternalValue();

        return new static($this->getInternalValue() + $value, $this->type);
    }

    /**
     * Returns a collection of the current collection values
     *
     * @return Collection
     */
    public function values()
    {
        return new static(array_values($this->getInternalValue()));
    }

    /**
     * OLD COLLECTION METHODS
     */

    /**
     * @param $key
     */
    public function remove($value, $strict = false)
    {
        while ($index = $this->search($value, $strict)) {
            $this->delete($index);
        }

        return $this;
    }

    /**
     * END OLD COLLECTION METHODS
     */

    /**
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer. The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->value);
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->value);
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->value);
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->value);
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated. Returns true on success or false on
     *              failure.
     */
    public function valid()
    {
        return !is_null(key($this->value));
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->value);
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure. The return value will be casted to boolean if non-boolean
     *                 was returned.
     */
    public function offsetExists($offset)
    {
        $internalValue = $this->getInternalValue();

        return empty($internalValue) ? false : array_key_exists($offset, $internalValue);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->value[$offset]);
    }

    /**
     * Convert a value to a Collection instance
     *
     * @param mixed $collection mixed Value can be a single value, an array or a Collection
     *
     * @return static
     */
    public static function cast($collection)
    {
        if ($collection instanceof Collection) {
            return $collection;
        }

        return new static($collection);
    }

    /**
     * Returns the type of a value
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function getValueType($value)
    {
        switch (true) {
            case is_scalar($value):
                $type = gettype($value);
                break;

            case is_array($value):
                $type = 'array';
                break;

            case is_object($value):
                $type = get_class($value);
                break;

            case is_resource($value):
                $type = 'resource';
                break;

            default:
                $type = 'unknown';
                break;
        }

        return $type;
    }

    /**
     * Tells whether the type must be checked
     *
     * @return bool
     */
    protected function mustRestrict(): bool
    {
        return !is_null($this->type);
    }

    /**
     * Check type restriction
     *
     * @param mixed[] $values
     *
     * @throws UnsupportedTypeException If value's type doesn't match
     */
    protected function checkRestriction(...$values)
    {
        if ($this->mustRestrict()) {
            foreach ($values as $value) {
                if (!$value instanceof $this->type) {
                    throw new UnsupportedTypeException(
                        sprintf(
                            'The value of type "%s" doesn\'t match type restriction "%s"',
                            $this->getValueType($value),
                            $this->type
                        )
                    );
                }
            }
        }
    }
}

