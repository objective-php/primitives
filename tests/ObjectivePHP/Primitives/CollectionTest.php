<?php

    namespace Tests\ObjectivePHP\Primitives;

    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Collection;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\Numeric;
    use ObjectivePHP\Primitives\String;

    class CollectionTest extends TestCase
    {

        public function testCollectionCanBeRestrictedToOneType()
        {
            $collection      = (new Collection)->restrictTo(Collection::class, false);
            $otherCollection = new Collection;
            $collection[]    = $otherCollection;

            $this->expectsException(function () use ($collection)
            {
                $collection[] = 'this is not a Collection object';
            }, Exception::class, null, Exception::COLLECTION_FORBIDDEN_VALUE);
        }

        public function testAnyValueCanBeAppendedToCollectionIfTypeIsDisabled()
        {
            $collection      = (new Collection)->restrictTo(Collection::class);
            $otherCollection = new Collection;
            $collection->append($otherCollection);
            $collection->restrictTo('mixed');
            $collection[] = 'any value';
            $this->assertEquals('any value', $collection[1]);
        }

        /**
         * @dataProvider dataProviderForTestTypeValidity
         */
        public function testTypeValidity($type, $valid)
        {
            $collection = new Collection();

            if (!is_null($valid))
            {
                $collection->restrictTo($type, false);
                $this->assertEquals($valid, $collection->getType());
            }
            else
            {
                $this->expectsException(function () use ($collection, $type)
                {
                    $collection->restrictTo($type);
                }, Exception::class, null, Exception::COLLECTION_INVALID_TYPE);

            }
        }

        public function dataProviderForTestTypeValidity()
        {
            return
                [
                    [\RecursiveDirectoryIterator::class, \RecursiveDirectoryIterator::class],
                    [\ArrayAccess::class, \ArrayAccess::class],
                    [false, false],
                    ['mixed', false],
                    [null, false],
                    ['UNKNOWN', null]
                ];
        }

        public function testStringTypeValidity()
        {
            $collection = new Collection();

            $collection->restrictTo(String::class, false)->addNormalizer(function(&$value)
            {
                // we add here a more restrictive normalizer than the default one
                if(is_string($value)) $value = new String($value);
            });
            $collection[] = 'scalar string';
            $collection[] = new String('another string');
            $this->assertInstanceOf(String::class, $collection[0]);
            $this->assertEquals('scalar string', (string) $collection[0]);
            $this->assertEquals('another string', (string) $collection[1]);

            $this->expectsException(function () use ($collection)
            {
                $collection[2] = 0x1;
            }, Exception::class, null, Exception::COLLECTION_FORBIDDEN_VALUE);

        }

        public function testCollectionTypeCanBeAnInterface()
        {
            $collection = (new Collection())->restrictTo(TestInterface::class);

            $this->assertEquals(TestInterface::class, $collection->getType());
        }

        public function testAllowedKeysCanBeDefinedAndFetched()
        {
            $collection = new Collection();

            $collection->allowed('allowed_key');
            $this->assertEquals(['allowed_key'], $collection->allowed());
            $collection->allowed(['a', 'b']);
            $this->assertEquals(['a', 'b'], $collection->allowed());

        }

        public function testOnlyAllowedKeysCanBeFilled()
        {
            $collection = new Collection();

            $collection->allowed('allowed_key');
            $collection['allowed_key'] = 'string';
            $this->assertEquals('string', $collection['allowed_key']);
            $this->expectsException(function () use ($collection)
            {
                $collection['illegal_key'] = 'test';
            }, Exception::class, null, Exception::COLLECTION_FORBIDDEN_KEY);

        }

        public function testOnlyAllowedKeysCanBeRead()
        {
            $collection = new Collection();

            $collection->allowed('allowed_key');

            $this->assertNull($collection['allowed_key']);


            $this->expectsException(function () use ($collection)
            {
                $collection['illegal_key'];
            }, Exception::class, null, Exception::COLLECTION_FORBIDDEN_KEY);
        }

        public function testEachLoopWithCallback()
        {
            $collection = new Collection([1, 2, 3]);

            $this->assertSame($collection, $collection->each(function ()
            {
            }));

            $this->assertEquals([2, 4, 6], $collection->each(function (&$value)
            {
                $value *= 2;
            })->getArrayCopy());

            $this->expectsException(function () use ($collection)
            {
                $collection->each('not callable');
            }, Exception::class, null, Exception::INVALID_CALLBACK);
        }

        public function testFilter()
        {
            $records    = [1, false, null, ''];
            $collection = new Collection($records);

            $this
                ->expectsException(function () use ($collection)
                {
                    $collection->filter('exception');
                }, Exception::class, null, Exception::INVALID_CALLBACK);

            // default behaviour: filter returns a new Collection
            $filtered = $collection->filter();
            $this->assertInstanceOf(Collection::class, $filtered);
            $this->assertNotSame($collection, $filtered);
            $this->assertEquals([1], $filtered->getArrayCopy());

            // alternative: it returns self
            $filtered = $collection->filter(true);
            $this->assertInstanceOf(Collection::class, $filtered);
            $this->assertSame($collection, $filtered);
            $this->assertEquals([1], $filtered->getArrayCopy());


            // other scenarii
            $records    = [1, 'test', 'test', ''];
            $collection = new Collection($records);
            $filtered   = $collection->filter(function ()
            {
                return false;
            });
            $this->assertInstanceOf(Collection::class, $filtered);
            $this->assertNotSame($collection, $filtered);
            $this->assertEquals([], $filtered->getArrayCopy());


            $filtered = $collection->filter(function ()
            {
                return false;
            }, true);
            $this->assertSame($collection, $filtered);
            $this->assertEquals([], $filtered->getArrayCopy());
        }

        public function testJoin()
        {
            $collection = (new Collection([new String('Objective'), new String('PHP')]))->restrictTo(String::class);

            $this->assertEquals('Objective PHP', $collection->join());
        }

        public function testAppendIsFluent()
        {
            $collection = new Collection();
            $result     = $collection->append('test');
            $this->assertSame($collection, $result);
        }

        public function testNormalizer()
        {
            $collection = new Collection(['a', 'b', 'c']);
            $collection->addNormalizer(function (&$value)
            {
                $value = strtoupper($value);
            });

            $this->assertEquals('A', $collection[0]);

            $collection->append('d');
            $this->assertEquals('D', $collection[3]);
        }

        public function testNormalizerStack()
        {
            $collection = new Collection(['a', 'b', 'C']);
            $collection->addNormalizer(function (&$value)
            {
                $value = strtolower($value);
            });
            $collection->addNormalizer(function (&$value)
            {
                $value = '_' . strtolower($value) . '_';
            });

            $this->assertEquals('_a_', $collection[0]);
            $this->assertEquals('_b_', $collection[1]);
            $this->assertEquals('_c_', $collection[2]);

            $collection->append('D');
            $this->assertEquals('_d_', $collection[3]);
        }

        public function testKeyNormalization()
        {
            $collection = new Collection(['X' => 'a', 'y' => 'b', 'Z' => 'C']);
            $collection->addNormalizer(function (&$value, &$key)
            {
                $key   = strtoupper($key);
                $value = strtolower($value);
            });

            // @todo allow key normalization for previously stored entries too!

            $collection['d'] = 'TEST';
            $this->assertEquals(['X' => 'a', 'Y' => 'b', 'Z' => 'c', 'D' => 'test'], $collection->getInternalValue());
        }

        public function testValidator()
        {
            $collection = new Collection(['a', 'b', 'c']);
            $collection->addValidator($validator = function ($value)
            {
                return strlen($value) == 1;
            });

            $this->assertAttributeEquals(Collection::cast([$validator]), 'validators', $collection);

            $this->expectsException(function () use ($collection)
            {
                $collection[] = 'invalid string!';
            }, Exception::class);
        }

        public function testCastingArray()
        {
            $value            = ['a', 'b', 'c'];
            $castedCollection = Collection::cast($value);

            $this->assertInstanceOf(Collection::class, $castedCollection);
            $this->assertEquals($value, $castedCollection->getInternalValue());
            $this->assertSame($castedCollection, Collection::cast($castedCollection));
        }

        public function testCastingArrayObject()
        {
            $value            = new \ArrayObject(['a', 'b', 'c']);
            $castedCollection = Collection::cast($value);
            $this->assertInstanceOf(Collection::class, $castedCollection);
            $this->assertEquals($value->getArrayCopy(), $castedCollection->getInternalValue());
            $this->assertSame($castedCollection, Collection::cast($castedCollection));
        }

        public function testDataMerging()
        {
            $data       = ['b' => 'y'];
            $collection = new Collection(['a' => 'x']);

            $collection->merge($data);


            $this->assertEquals(Collection::cast(['a' => 'x', 'b' => 'y']), $collection);
            $collection->merge(['a' => 'z']);
            $this->assertEquals(Collection::cast(['a' => 'z', 'b' => 'y']), $collection);
        }

        public function testCollectionsAddition()
        {
            $collection = new Collection(['a' => 'x']);
            $collection->add(['a' => 'ignored', 'b' => 'y']);
            $this->assertEquals(Collection::cast(['a' => 'x', 'b' => 'y']), $collection);
        }

        public function testValuesExport()
        {
            $collection = new Collection(['a' => 'x']);

            $values = $collection->getValues();

            $this->assertEquals(Collection::cast([0 => 'x']), $values);
        }

        public function testKeysExport()
        {
            $collection = new Collection(['a' => 'x']);

            $values = $collection->getKeys();

            $this->assertEquals(Collection::cast([0 => 'a']), $values);
        }

        public function testIsEmpty()
        {
            $collection = new Collection();

            $this->assertEquals(0, count($collection));
            $this->assertTrue($collection->isEmpty());

            $collection->append('some value');

            $this->assertEquals(1, count($collection));
            $this->assertFalse($collection->isEmpty());
        }

    }


    /*********************
     * HELPERS
     ********************/
    interface TestInterface {

}