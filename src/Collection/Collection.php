<?php

    namespace ObjectivePHP\Primitives\Collection;

    use ArrayObject;
    use ObjectivePHP\Primitives\AbstractPrimitive;
    use ObjectivePHP\Primitives\Collection\Normalizer\ObjectNormalizer;
    use ObjectivePHP\Primitives\Collection\Normalizer\PrimitiveNormalizer;
    use ObjectivePHP\Primitives\Collection\Validator\ObjectValidator;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\Merger\MergerInterface;
    use ObjectivePHP\Primitives\PrimitiveInterface;
    use ObjectivePHP\Primitives\String\String;

    /**
     * Class Collection
     *
     * @package ObjectivePHP\Primitives
     */
    class Collection extends \ArrayObject implements PrimitiveInterface
    {

        /**
         * Primitive type
         */
        const TYPE = 'collection';

        /**
         * Special value to release restriction
         */
        const MIXED = 'mixed';

        /**
         * Collections content type
         *
         * @var string $type
         */
        protected $type;

        /**
         * Allowed keys
         *
         * @var $allowedKeys Collection An empty array means all keys are allowed
         */
        protected $allowedKeys = [];

        /**
         * @var Collection
         */
        protected $normalizers = [];

        /**
         * @var Collection
         */
        protected $keyNormalizers = [];

        /**
         * @var Collection
         */
        protected $validators = [];

        protected $mergers    = [];

        /**
         * @param array  $input
         * @param int    $flags
         * @param string $iterator_class
         */
        public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator")
        {
            $this->setFlags($flags);
            $this->setIteratorClass($iterator_class);
            $this->exchangeArray($input);

        }

        /**
         * Set collection value
         *
         * @param $value
         *
         * @todo check value type ; only allow array, Iterator and Collection
         *
         * @return $this
         */
        public function setInternalValue($value)
        {

            $this->exchangeArray($value);

            return $this;
        }

        /**
         * Get collection value (as an array)
         *
         * @return array
         */
        public function getInternalValue()
        {
            return $this->toArray();
        }

        /**
         * Alias of self::getInternalValue()
         *
         * @return array
         */
        public function toArray()
        {
            $array = $this->getArrayCopy();

            foreach ($array as &$value)
            {
                if ($value instanceof Collection)
                {
                    $value = $value->toArray();
                }
            }

            return $array;
        }

        /**
         * Set or retrieve collection type
         *
         * @param string $type      Type of the collection. If null, current type is returned
         *
         * @param bool   $normalize If false, no normalizer will be automatically added - only validator
         *
         * @return $this
         * @throws Exception
         */
        public function restrictTo($type, $normalize = true)
        {
            // unset type
            if (!$type || $type == self::MIXED)
            {
                return $this->clearRestrictions();
            }

            // set new type
            if (!$this->getValidators()->isEmpty() || !$this->getNormalizers()->isEmpty())
            {
                throw new Exception('Class restriction can not be set if there is already Normalizer and/or Validator attached to the collection', Exception::COLLECTION_INVALID_TYPE);
            }

            // add normalizer (if type is a class - interfaces cannot be normalized
            if ($normalize && !interface_exists($type))
            {
                switch (true)
                {
                    case (!class_exists($type)):
                        throw new Exception(sprintf('Class "%s" does not exist', $type), Exception::COLLECTION_INVALID_TYPE);

                    case (AbstractPrimitive::isPrimitive($type)):
                        $normalizer = new PrimitiveNormalizer($type);
                        break;

                    default:
                        $normalizer = new ObjectNormalizer($type);
                        break;
                }

                $this->addNormalizer($normalizer);
            }

            $this->addValidator(new ObjectValidator($type));

            $this->type = (string) $type;

            return $this;
        }

        /**
         * @return $this
         */
        public function clearRestrictions()
        {
            $this->validators  = [];
            $this->normalizers = [];

            return $this;
        }

        /**
         * Returns collection type
         *
         * @see {Collection::of()}
         *
         * @return string
         */
        public function getType()
        {
            return $this->type;
        }

        /**
         * ArrayAccess implementation
         *
         * @param mixed $index
         * @param mixed $value
         *
         * @throws Exception
         */
        public function offsetSet($index, $value)
        {
            $this->set($index, $value);
        }

        /**
         * ArrayAccess implementation
         *
         * @param mixed $index
         *
         * @return mixed|null
         * @throws Exception
         */
        public function offsetGet($index)
        {
            return $this->get($index);
        }

        /**
         * Set or retrieve allowed keys
         *
         * @param array|string $keys Key(s) allowed. Pass en empty array to remove restrictions on keys. If null,
         *                           current allowed keys are returned
         *
         * @return $this|array
         */
        public function setAllowedKeys($keys)
        {
            $keys = Collection::cast($keys);

            $this->allowedKeys = $keys;

            return $this;
        }

        /**
         * @return Collection
         */
        public function getAllowedKeys()
        {
            return $this->allowedKeys;
        }

        /**
         * @param $key
         *
         * @return bool
         */
        public function isKeyAllowed($key)
        {
            return (empty($this->allowedKeys) ? true : $this->getAllowedKeys()->contains($key));
        }

        /**
         * Iterates collection. Value is passed by reference in the callback.
         *
         * @param $callable
         *
         * @throws Exception
         * @return $this
         */
        public function each($callable)
        {
            if (!is_callable($callable))
            {
                throw new Exception(sprintf('Parameter of type  %s is not callable', gettype($callable)),
                    Exception::INVALID_CALLBACK
                );
            }
            foreach ($this as $key => &$val)
            {
                $callable($val, $key);
            }

            return $this;
        }

        /**
         * Returns a new filtered collection
         *
         * @param callable $callable Optional callable to filter the data
         *
         * @throws Exception
         * @return $this
         */
        public function filter($callable = null)
        {
            if (null !== $callable && !is_callable($callable))
            {
                throw new Exception(sprintf('Parameter of type  %s is not callable', gettype($callable)),
                    Exception::INVALID_CALLBACK
                );
            }

            $array = is_callable($callable)
                ? array_filter($this->getArrayCopy(), $callable, ARRAY_FILTER_USE_BOTH)
                : array_filter($this->getArrayCopy());


            $this->exchangeArray($array);

            return $this;
        }

        /**
         * FLip the collection (invert keys and values)
         *
         * @return $this
         */
        public function flip()
        {

            // get null valued data
            $unvaluedEntries = $this->copy()->filter(function($value){
                return !$value;
            })->keys();

            $this->filter();

            $this->setInternalValue(array_merge(array_flip($this->toArray()), $unvaluedEntries->toArray()));

            return $this;
        }

        /**
         * Return value to serialize on json_encode calls
         *
         * @see {@\JsonSerializable}
         * @return array
         */
        public function jsonSerialize()
        {
            return $this->getArrayCopy();
        }

        /**
         * Apply a callback to primitive's internal value
         *
         * @param callable $callback
         *
         * @return $this
         */
        public function apply(callable $callback)
        {
            $this->setInternalValue($callback($this->toArray()));

            return $this;
        }

        /**
         * Return a cloned primitive
         *
         * @return static
         */
        public function copy()
        {
            return clone $this;
        }

        /**
         * Returns a String generated from items concatenation
         *
         * @param string $glue
         *
         * @todo loads of UT are missing yet!
         *
         * @return String
         */
        public function join($glue = ' ')
        {
            $joinedString = new String(implode($glue, $this->toArray()));

            return $joinedString;
        }

        /**
         * Shunt native append() method to make it fluent
         *
         * @param mixed $values
         *
         * @return $this
         */
        public function append(...$values)
        {
            foreach ($values as $value)
            {
                $this[] = $value;
            }

            return $this;
        }

        /**
         * Return normalizers
         *
         * @return Collection
         */
        public function getNormalizers()
        {
            // initialize normalizers collection
            $this->normalizers = Collection::cast($this->normalizers);

            return $this->normalizers;
        }

        /**
         * Return key normalizers
         *
         * @return Collection
         */
        public function getKeyNormalizers()
        {
            // initialize normalizers collection
            $this->keyNormalizers = Collection::cast($this->keyNormalizers);

            return $this->keyNormalizers;
        }

        /**
         * Return validators
         *
         * @return Collection
         */
        public function getValidators()
        {
            // initialize validators collection
            $this->validators = Collection::cast($this->validators);

            return $this->validators;
        }

        /**
         * Add a normalizer
         *
         * @param callable $normalizer
         *
         * @return $this
         */
        public function addNormalizer(callable $normalizer)
        {
            // applies normalizer to currently stored entries
            foreach ($this as &$value)
            {
                $normalizer($value);
            }

            // stack the new normalizer
            $this->getNormalizers()[] = $normalizer;

            return $this;
        }

        /**
         * Add a key normalizer
         *
         * @param callable $normalizer
         *
         * @return $this
         */
        public function addKeyNormalizer(callable $normalizer)
        {
            // applies normalizer to currently stored entries
            $data = $this->toArray();
            $this->clear();

            foreach ($data as $key => $value)
            {
                $normalizer($key);

                $this->set($key, $value);
            }

            // stack the new normalizer
            $this->getKeyNormalizers()[] = $normalizer;

            return $this;
        }


        /**
         * Clear all normalizers
         *
         * @return $this
         */
        public function clearNormalizers()
        {
            $this->normalizers = new Collection();

            return $this;
        }

        /**
         * Clear all key normalizers
         *
         * @return $this
         */
        public function clearKeyNormalizers()
        {
            $this->keyNormalizers = new Collection();

            return $this;
        }

        /**
         * Add a validator
         *
         * @param callable $validator
         *
         * @return $this
         *
         * @throws Exception
         */
        public function addValidator(callable $validator)
        {

            // match validator against currently stored entries
            foreach ($this->toArray() as $key => $value)
            {
                if (!$validator($value))
                {
                    throw new Exception('Value #' . $key . ' did not pass validation', Exception::COLLECTION_FORBIDDEN_VALUE);
                }
            }

            // stack the validator
            $this->getValidators()[] = $validator;

            return $this;
        }

        /**
         * Clear validators
         *
         * @return $this
         */
        public function clearValidators()
        {
            $this->validators = new Collection();

            return $this;
        }

        /**
         * Convert a value to a Collection instance
         *
         * @param $collection mixed Value can be a single value, an array or a Collection
         *
         * @return static
         */
        static public function cast($collection)
        {
            if ($collection instanceof Collection)
            {
                return $collection;
            }

            return new static($collection);
        }

        /**
         * Reset internal value to an empty array
         */
        public function clear()
        {
            parent::exchangeArray([]);

            return $this;
        }

        /**
         * Merge a collection into another
         *
         * @param $data mixed Data to merge (will be casted to Collection)
         *
         * @return $this
         */
        public function merge($data)
        {
            // force data conversion to array
            $data = Collection::cast($data)->toArray();
            $mergers = $this->getMergers();

            if (!$mergers->isEmpty())
            {
                // prepare data by manually merging some keys
                foreach ($mergers as  $key => $merger)
                {
                    if(isset($data[$key]) && isset($this[$key]))
                    {
                        $data[$key] = $merger->merge($this[$key], $data[$key]);
                    }
                }
            }

            $this->setInternalValue(array_merge($this->toArray(), $data));

            return $this;
        }

        /**
         * Add a collection
         *
         * This uses the same rules as "array + array" operation in native PHP
         *
         * @param $data
         *
         * @return $this
         */
        public function add($data)
        {
            // force data conversion to array
            $data = Collection::cast($data)->toArray();

            $this->setInternalValue($this->toArray() + $data);

            return $this;
        }

        /**
         * Return a new collection with a numeric index
         *
         * Return a new Collection with same data but without indices
         */
        public function values()
        {
            return new Collection(array_values($this->toArray()));
        }

        /**
         * Return a new Collection with current keys as values
         */
        public function keys()
        {
            return new Collection(array_keys($this->toArray()));
        }

        /**
         * Is a given index set on the Collection?
         *
         * @param $key
         *
         * @return bool
         */
        public function has($key)
        {
            return parent::offsetExists($key);
        }

        /**
         * Return the value matching the requested key or a default value
         *
         * Ease fluent interface
         *
         * @param $key
         * @param null|mixed $default
         *
         * @return mixed|null
         * @throws Exception
         */
        public function get($key, $default = null)
        {
            if ($this->lacks($key))
            {
                if (!$this->isKeyAllowed($key))
                {
                    throw new Exception(sprintf('Forbidden key: "%s"', $key), Exception::COLLECTION_FORBIDDEN_KEY);
                }
            }

            return $this->has($key) ? parent::offsetGet($key) : $default;
        }

        /**
         * Define a key and associate a value to it
         *
         * @param $key
         * @param $value
         *
         * @return $this
         * @throws Exception
         */
        public function set($key, $value)
        {
            // normalize value
            $this->getNormalizers()->each(function ($normalizer) use (&$value)
            {
                $normalizer($value);
            })
            ;

            // normalize key
            $this->getKeyNormalizers()->each(function ($normalizer) use (&$key)
            {
                $normalizer($key);
            })
            ;

            // check key validity
            if (!$this->isKeyAllowed($key))
            {
                throw new Exception('Illegal key: ' . $key, Exception::COLLECTION_FORBIDDEN_KEY);
            }

            // validate value
            $this->getValidators()->each(function ($validator) use ($value, &$isValid)
            {
                if (!$validator($value))
                {
                    throw new Exception('New value did not pass validation', Exception::COLLECTION_FORBIDDEN_VALUE);
                }
            })
            ;

            parent::offsetSet($key, $value);

            return $this;
        }

        /**
         * Is the given key missing in the Collection?
         *
         * @param $key
         *
         * @return bool
         */
        public function lacks($key)
        {
            return !$this->has($key);
        }

        /**
         * Search a value in the Collection and return the associated key
         *
         * If search is not strict, strings will be compared ignoring case
         *
         * @param $value
         * @param bool $strict
         *
         * @return mixed
         *
         * @throws Exception
         */
        public function search($value, $strict = false)
        {
            if (!$strict)
            {
                $values = clone $this;
                $values->each(function (&$value)
                {
                    if (is_string($value))
                    {
                        $value = strtolower($value);
                    }
                });
                $value = strtolower($value);
            }
            else
            {
                $values = $this;
            }

            return array_search($value, $values->toArray(), (bool) $strict);
        }

        /**
         * Does the collection contains a given value
         *
         * @param $value
         * @param $strict bool
         *
         * @return bool
         */
        public function contains($value, $strict = false)
        {
            // array_search returns false or the first matching key
            return (is_bool($this->search($value, $strict)) ? false : true);
        }

        /**
         * Return the first element of the collection
         *
         * The first element is the first having been added to the collection,
         * not necessarily the one with the lowest index (for numerical indices)
         *
         * @return mixed
         */
        public function first()
        {
            $values = $this->getArrayCopy();
            reset($values);
            $lastKey = key($values);

            return $this->get($lastKey);
        }

        /**
         * Return the last element of the collection
         *
         * The first element is the first having been added to the collection,
         * not necessarily the one with the highest index (for numerical indices)
         *
         * @return mixed Last appended item
         */
        public function last()
        {

            $values = $this->getArrayCopy();
            end($values);
            $lastKey = key($values);

            return $this->get($lastKey);
        }

        /**
         * Put a value at the beginning of the collection
         *
         * @param mixed $values
         *
         * @return $this
         */
        public function prepend(...$values)
        {
            $this->exchangeArray(array_merge($values, $this->getArrayCopy()));

            return $this;
        }

        /**
         * Replace the internal value with a new data set
         *
         * @param mixed $data
         *
         * @return $this
         */
        public function exchangeArray($data)
        {
            if ($data instanceof ArrayObject)
            {
                $data = $data->getArrayCopy();
            }

            if ($data instanceof \Iterator)
            {
                $data = iterator_to_array($data);
            }

            if (!is_array($data))
            {
                $data = [$data];
            }

            $this->clear();

            foreach($data as $key => $value)
            {
                $this->set($key, $value);
            }

            return $this;
        }

        /**
         * Proxy to exchangeArray
         *
         * @param $data
         *
         * @return $this
         */
        public function fromArray($data)
        {
            return $this->exchangeArray($data);
        }

        /**
         * Is the Collection empty?
         *
         * @return bool
         */
        public function isEmpty()
        {
            return !(bool) count($this);
        }

        /**
         * Add a merger
         *
         * @param $keys
         * @param MergerInterface $merger
         *
         * @return $this
         */
        public function addMerger($keys, MergerInterface $merger)
        {
            $mergers = $this->getMergers();
            $keys    = Collection::cast($keys);

            foreach ($keys as $key)
            {
                // normalize key first
                $this->getKeyNormalizers()->each(function($normalizer) use(&$key) { $normalizer($key); });

                $mergers[$key] = $merger;
            }

            $this->mergers = $mergers;

            return $this;
        }

        /**
         * Return all previously added mergers
         *
         * @return Collection
         */
        public function getMergers()
        {
            return Collection::cast($this->mergers);
        }


        /**
         * Replace the value of a key by a new one
         *
         * @param $from
         * @param $to
         *
         * @return $this
         * @throws \Exception
         */
        public function rename($from, $to)
        {
            $arrayCopy = $this->getArrayCopy();

            if (!array_key_exists($from, $arrayCopy))
                throw new Exception(sprintf('The key %s was not found.', $from), Exception::INVALID_PARAMETER);

            $keys = array_keys($arrayCopy);
            $keys[array_search($from, $keys)] = $to;

            $this->exchangeArray(array_combine($keys, $arrayCopy));

            return $this;

        }
    }

