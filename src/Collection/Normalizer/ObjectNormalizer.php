<?php
    namespace ObjectivePHP\Primitives\Collection\Normalizer;

    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Primitives\Exception;

    class ObjectNormalizer
    {
        /**
         * @var string
         */
        protected $className;

        public function __construct($className)
        {
            if (!class_exists($className))
            {
                throw new Exception(sprintf('Class "%s" does not exist', $className), Exception::NORMALIZER_INVALID_CLASS);
            }

            $this->className = (string) $className;
        }

        public function __invoke(&$value)
        {
            $className = $this->className;

            if (!$value instanceof $className)
            {
                $value = new $className(...Collection::cast($value)->values()->getInternalValue());
            }
        }
    }