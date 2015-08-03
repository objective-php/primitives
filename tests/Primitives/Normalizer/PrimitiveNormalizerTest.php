<?php

    namespace Tests\ObjectivePHP\Primitives\Normalizer;

    use helpers\NonPrimitiveClass;
    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Collection\Normalizer\PrimitiveNormalizer;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\String\String;

    class PrimitiveNormalizerTest extends TestCase
    {

        public function testNormalizerRejectsNonPrimitiveClass()
        {
            $this->expectsException(function ()
            {
                new PrimitiveNormalizer(NonPrimitiveClass::class);
            }, Exception::class, null, Exception::NORMALIZER_INCOMPATIBLE_CLASS);
        }

        public function testNormalizerCastsValueToPrimitiveType()
        {
            $value = 'string';
            $normalizer = new PrimitiveNormalizer(String::class);

            $normalizer($value);

            $this->assertInstanceOf(String::class, $value);
            $this->assertEquals('string', $value->getInternalValue());
        }


    }

    namespace helpers;

    class NonPrimitiveClass
    {

    }