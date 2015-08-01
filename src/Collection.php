<?php

    namespace ObjectivePHP\Primitives;

    use ArrayObject;
    use ObjectivePHP\Primitives\Normalizer\ObjectNormalizer;
    use ObjectivePHP\Primitives\Normalizer\PrimitiveNormalizer;
    use ObjectivePHP\Primitives\Validator\ObjectValidator;

    class Collection extends \ArrayObject implements PrimitiveInterface
    {

        const TYPE  = 'collection';
        const MIXED = 'mixed';

        /**
         * Collections content's type
         *
         * @var string $type
         */
        protected $type;

        /**
         * Allowed keys
         *
         * @var $allowed array An empty array means all keys are allowed
         */
        protected $allowed = [];

        /**
         * @var Collection
         */
        protected $normalizers = [];

        /**
         * @var Collection
         */
        protected $validators = [];


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
            return $this->getArrayCopy();
        }

        /**
         * Set or retrieve collection type
         *
         * @param string $type      Type of the collection. If null, current type is returned
         *
         * @param bool   $normalize    If false, no normalizer will be automatically added - only validator
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

            // add normalizer
            if($normalize)
            {
                switch (true)
                {
                    case (!class_exists($type) && !interface_exists($type)):
                        throw new Exception(sprintf('Class or interface "%s" does not exist', $type), Exception::COLLECTION_INVALID_TYPE);

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

        public function clearRestrictions()
        {
            $this->validators = [];
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
            // normalize value
            $this->getNormalizers()->each(function ($normalizer) use (&$value, &$index)
            {
                $normalizer($value, $index);
            });

            // check key validity
            if ($this->allowed && !in_array($index, $this->allowed))
            {
                throw new Exception('Illegal key: ' . $index, Exception::COLLECTION_FORBIDDEN_KEY);
            }

            // validate value
            $this->getValidators()->each(function ($validator) use ($value, &$isValid)
            {
                if (!$validator($value))
                {
                    throw new Exception('New value did not pass validation', Exception::COLLECTION_FORBIDDEN_VALUE);
                }
            });

            parent::offsetSet($index, $value);
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
            if (!isset($this[$index]))
            {
                if ($this->allowed && !in_array($index, $this->allowed))
                {
                    throw new Exception('Illegal key: ' . $index, Exception::COLLECTION_FORBIDDEN_KEY);
                }
                else
                {
                    return null;
                }
            }
            else
            {
                return parent::offsetGet($index);
            }
        }

        /**
         * Set or retrieve allowed keys
         *
         * @param array|string $keys Key(s) allowed. Pass en empty array to remove restrictions on keys. If null,
         *                           current allowed keys are returned
         *
         * @return $this|array
         */
        public function allowed($keys = null)
        {
            if (is_null($keys))
            {
                return $this->allowed;
            }

            if (!is_array($keys)) $keys = [$keys];

            $this->allowed = $keys;

            return $this;
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
         * @param callable $callable A Optional callable
         * @param bool     $apply    Is the filter must be applied to the current collection or return a new collection
         *                           instance
         *
         * @throws Exception
         * @return Collection
         */
        public function filter($callable = null, $apply = false)
        {
            // Exchange arguments: filter with no callback by ref
            if (is_bool($callable))
            {
                $apply    = $callable;
                $callable = null;
            }

            if (null !== $callable && !is_callable($callable))
            {
                throw new Exception(sprintf('Parameter of type  %s is not callable', gettype($callable)),
                    Exception::INVALID_CALLBACK
                );
            }

            $array = is_callable($callable)
                ? array_filter($this->getArrayCopy(), $callable)
                : array_filter($this->getArrayCopy());

            if ($apply === true)
            {
                $this->exchangeArray($array);

                return $this;
            }

            return new static($array);
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
         * @return PrimitiveInterface
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
         * @param mixed $value
         *
         * @return $this
         */
        public function append($value)
        {
            $this[] = $value;

            return $this;
        }

        /**
         * @return Collection
         */
        public function getNormalizers()
        {
            // initialize normalizers collection
            $this->normalizers = Collection::cast($this->normalizers);

            return $this->normalizers;
        }

        /**
         * @return Collection
         */
        public function getValidators()
        {
            // initialize validators collection
            $this->validators = Collection::cast($this->validators);
            return $this->validators;
        }

        /**
         * @param callable $normalizer
         *
         * @return $this
         */
        public function addNormalizer(callable $normalizer)
        {
            // applies normalizer to currently stored entries
            $data = $this->toArray();
            $this->clear();

            foreach ($data as $key => $value)
            {
                $normalizer($value, $key);
                $this[$key] = $value;
            }

            // stack the new normalizer
            $this->getNormalizers()[] = $normalizer;

            return $this;
        }

        /**
         * @param callable $validator
         *
         * @return $this
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

        static public function cast($collection)
        {
            if ($collection instanceof Collection)
            {
                return $collection;
            }

            return new static($collection);
        }

        /**
         * Clear content
         */
        public function clear()
        {
            $this->setInternalValue([]);
        }

        /**
         * Wrapper for of array_merge
         *
         * @param $data
         */
        public function merge($data)
        {
            // force data conversion to array
            $data = Collection::cast($data)->toArray();

            $this->setInternalValue(array_merge($this->toArray(), $data));

            return $this;
        }

        /**
         * Wrapper for of + on two arrays
         *
         * @param $data
         */
        public function add($data)
        {
            // force data conversion to array
            $data = Collection::cast($data)->toArray();

            $this->setInternalValue($this->toArray() + $data);

            return $this;
        }

        /**
         * Wrapper for of \array_values
         * 
         * Return a new Collection with same data but without indices
         */
        public function getValues()
        {
            return new Collection(array_values($this->toArray()));
        }

        /**
         * Return a new Collection with current indices as values
         */
        public function getKeys()
        {
            return new Collection(array_keys($this->toArray()));
        }

        /**
         * Wrapper for of \array_has_key()
         * 
         * @param $key
         * 
         * @return bool
         */
        public function has($key)
        {
            return array_key_exists($key, $this->toArray());
        }

        /**
         * Custom implementation of \array_search()
         *
         * If search is not strict, strings will be compared ignoring case
         *
         * @param $value
         * 
         * @return mixed
         */
        public function search($value, $strict = false)
        {
            if(!$strict)
            {
                $values = clone $this;
                $values->each(function(&$value) { if(is_string($value)) { $value = strtolower($value);}});
                $value = strtolower($value);
            }
            else
            {
                $values = $this;
            }

            return array_search($value, $values->toArray(), (bool) $strict);
        }

        /**
         * Wrapper for \in_array()
         *
         * @param $value
         * @return bool
         */
        public function contains($value, $strict = false)
        {
            return (bool) $this->search($value, $strict);
        }

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

            parent::exchangeArray($data);

            return $this;
        }

        /**
         * @return bool
         */
        public function isEmpty()
        {
            return !(bool) count($this);
        }
    }

