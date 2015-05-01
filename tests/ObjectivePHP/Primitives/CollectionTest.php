<?php

    namespace ObjectivePHP\Primitives\tests\units;

    use mageekguy\atoum;
    use ObjectivePHP\Primitives\Exception;
    use ObjectivePHP\Primitives\Numeric;
    use ObjectivePHP\Primitives\String;
    use ObjectivePHP\Primitives\Collection as ActualCollection;

    class Collection extends atoum\test
    {

        public function testTypeCanBeSetOnlyOnceOrRemoved()
        {
            $collection = new ActualCollection();

            // set collection type
            $collection->of('ArrayObject');

            // cancel collection typing
            $collection->of('mixed');

            $this->exception(function () use ($collection)
            {
                // trying to set collection type again will raise an error
                $collection->of('ArrayObject');
            })->isInstanceOf(Exception::class);
        }

        public function testCollectionCanBeLimitedToOneType()
        {
            $collection      = (new \ObjectivePHP\Primitives\Collection)->of(ActualCollection::class);
            $otherCollection = new \ObjectivePHP\Primitives\Collection;
            $collection[]    = $otherCollection;

            $this->exception(function () use ($collection)
            {
                $collection[] = 'this is not a Collection object';
            })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);
        }

        public function testAnyValueCanBeAppendedToCollectionIfTypeIsDisabled()
        {
            $collection      = (new \ObjectivePHP\Primitives\Collection)->of(ActualCollection::class);
            $otherCollection = new \ObjectivePHP\Primitives\Collection;
            $collection[]    = $otherCollection;
            $collection->of('mixed');
            $collection[] = 'any value';
            $this->string($collection[1])->isEqualTo('any value');
        }

        public function testTypeValidity($type, $valid)
        {
            $collection = new ActualCollection();

            if (!is_null($valid))
            {
                $collection->of($type);
                $this->variable($collection->of())->isEqualTo($valid);
            }
            else
            {
                $this->exception(function () use ($collection, $type)
                {
                    $collection->of($type);
                })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_TYPE_IS_INVALID);

            }
        }

        protected function testTypeValidityDataProvider()
        {
            return
                [
                    ['integer', 'numeric'],
                    ['int', 'numeric'],
                    ['string', 'string'],
                    [ActualCollection::class, ActualCollection::class],
                    [false, false],
                    ['UNEXISTING_CLASS', null]
                ];
        }

        public function testNumericTypeValidity()
        {
            $collection = new ActualCollection();

            $collection->of('int')->offsetSet(0, 3);
            $this->object($collection[0])->isInstanceOf(Numeric::class);
            $this->variable($collection[0]->getInternalValue())->isEqualTo(3);

            $this->exception(function () use ($collection)
            {
                $collection[1] = 'this is not an integer';
            })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);

        }

        public function testStringTypeValidity()
        {
            $collection = new ActualCollection();

            $collection->of('string');
            $collection[] = 'string';
            $collection[] = new String('another string');
            $this->string((string) $collection[0])->isEqualTo('string');
            $this->object($collection[0])->isInstanceOf(String::class);
            $this->string((string) $collection[1])->isEqualTo('another string');

            $this->exception(function () use ($collection)
            {
                $collection[2] = 0x1;
            })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);

        }

        public function testAllowedKeysCanBeDefinedAndFetched()
        {
            $collection = new ActualCollection();

            $collection->allowed('allowed_key');
            $this->array($collection->allowed())->isEqualTo(['allowed_key']);
            $collection->allowed(['a', 'b']);
            $this->array($collection->allowed())->isEqualTo(['a', 'b']);

        }

        public function testOnlyAllowedKeysCanBeFilled()
        {
            $collection = new ActualCollection();

            $collection->allowed('allowed_key');
            $collection['allowed_key'] = 'string';
            $this->string($collection['allowed_key'])->isEqualTo('string');
            $this->exception(function () use ($collection)
            {
                $collection['forbidden_key'] = 'test';
            })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_FORBIDDEN_KEY);

        }

        public function testOnlyAllowedKeysCanBeRead()
        {
            $collection = new ActualCollection();

            $collection->allowed('allowed_key');

            $this->variable($collection['allowed_key'])->isEqualTo(null);


            $this->exception(function () use ($collection)
            {
                $collection['illegal_key'];
            })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_FORBIDDEN_KEY);
        }

        public function testEachLoopWithCallback()
        {
            $collection = new ActualCollection([1, 2, 3]);

            $this
                ->object($collection->each(function ()
                {
                }))
                ->isIdenticalTo($collection)
                ->array($collection->each(function (&$value)
                {
                    $value *= 2;
                })->getArrayCopy())
                ->isEqualTo([2, 4, 6])
                ->exception(function () use ($collection)
                {
                    $collection->each('not callable');
                })
                ->isInstanceOf(Exception::class)
                ->hasCode(Exception::INVALID_CALLBACK);
        }

        public function testFilter()
        {
            $records    = [1, false, null, ''];
            $collection = $collection = new ActualCollection($records);

            // invalid filter
            $this
                ->exception(function () use ($collection)
                {
                    $collection->filter('exception');
                })
                ->isInstanceOf(Exception::class)
                ->hasCode(Exception::INVALID_CALLBACK);


            $this
                ->object($filtered = $collection->filter())
                ->isInstanceOf(get_class($collection))
                ->isNotIdenticalTo($collection)
                ->array($filtered->getArrayCopy())
                ->isEqualTo([1])
                ->object($collection->filter(true))
                ->isIdenticalTo($collection)
                ->array($collection->getArrayCopy())
                ->isEqualTo([1]);

            $records    = [1, 'test', 'test', ''];
            $collection = new ActualCollection($records);
            $this
                ->object($filtered = $collection->filter(function ()
                {
                    return false;
                }))
                ->isInstanceOf(get_class($collection))
                ->isNotIdenticalTo($collection)
                ->array($filtered->getArrayCopy())
                ->isEqualTo([])
                ->object($filtered = $collection->filter(function ()
                {
                    return false;
                }, true))
                ->isIdenticalTo($collection)
                ->array($filtered->getArrayCopy())
                ->isEqualTo([]);
        }

        public function testJoin()
        {
            $collection = (new ActualCollection([new String('Objective'), new String('PHP')]))->of(String::class);

            $this->string($collection->join()->getInternalValue())->isEqualTo('Objective PHP');
        }
}