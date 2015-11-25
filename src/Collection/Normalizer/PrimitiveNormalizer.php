<?php

    namespace ObjectivePHP\Primitives\Collection\Normalizer;


    use ObjectivePHP\Primitives\AbstractPrimitive;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\PrimitiveInterface;

    /**
     * Class PrimitiveNormalizer
     * @package ObjectivePHP\Primitives\Collection\Normalizer
     */
    class PrimitiveNormalizer extends ObjectNormalizer
    {

        /**
         * @param $primitive
         * @throws Exception
         */
        public function __construct($primitive)
        {
            // set class name and checks it exists
            parent::__construct($primitive);

            // extract string from Str instance if needed
            $primitive = (string) $primitive;

            if (!AbstractPrimitive::isPrimitive($primitive))
            {
                throw new Exception(sprintf('"%s" does not implements %s', $primitive, PrimitiveInterface::class), Exception::NORMALIZER_INCOMPATIBLE_CLASS);
            }

        }

    }