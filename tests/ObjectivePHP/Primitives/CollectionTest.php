<?php

namespace ObjectivePHP\Primitives\tests\units;

use mageekguy\atoum;
use ObjectivePHP\Primitives\Exception;
use ObjectivePHP\Primitives\String;

require_once __DIR__ . '/../../autoload.php';

class Collection extends atoum\test
{

    public function testTypeCanBeSetOnlyOnceOrRemoved()
    {
        $collection = new \ObjectivePHP\Primitives\Collection();

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
        $collection = (new \ObjectivePHP\Primitives\Collection)->of('ObjectivePHP\Primitives\Collection');
        $otherCollection = new \ObjectivePHP\Primitives\Collection;
        $collection[] = $otherCollection;

        $this->exception(function () use ($collection)
        {
            $collection[] = 'this is not a Collection object';
        })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);
    }

    public function testAnyValueCanBeAppendedToCollectionIfTypeIsDisabled()
    {
        $collection = (new \ObjectivePHP\Primitives\Collection)->of('ObjectivePHP\Primitives\Collection');
        $otherCollection = new \ObjectivePHP\Primitives\Collection;
        $collection[]    = $otherCollection;
        $collection->of('mixed');
        $collection[] = 'any value';
        $this->string($collection[1])->isEqualTo('any value');
    }

    public function testTypeValidity($type, $valid)
    {
        $collection = new \ObjectivePHP\Primitives\Collection();

        if(!is_null($valid))
        {
            $collection->of($type);
            $this->variable($collection->type())->isEqualTo($valid);
        }
        else
        {
            $this->exception(function() use($collection, $type)
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
            ['\ObjectivePHP\Primitives\Collection', '\ObjectivePHP\Primitives\Collection'],
            [false, false],
            ['UNKNOWN', null]
        ];
    }

    public function testIntegerTypeValidity()
    {
        $collection = new \ObjectivePHP\Primitives\Collection();

        $collection->of('int')->offsetSet(0, 3);
        $this->integer($collection[0])->isEqualTo(3);

        $this->exception(function () use ($collection)
        {
            $collection[1] = 'this is not an integer';
        })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);

    }

    public function testStringTypeValidity()
    {
        $collection = new \ObjectivePHP\Primitives\Collection();

        $collection->of('string');
        $collection[] = 'string';
        $collection[] = new String('another string');
        $this->string($collection[0])->isEqualTo('string');
        $this->string((string) $collection[1])->isEqualTo('another string');

        $this->exception(function () use ($collection)
        {
            $collection[2] = 0x1;
        })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_VALUE_DOES_NOT_MATCH_TYPE);

    }

    public function testAllowedKeysCanBeDefinedAndFetched()
    {
        $collection = new \ObjectivePHP\Primitives\Collection();

        $collection->allowed('allowed_key');
        $this->array($collection->allowed())->isEqualTo(['allowed_key']);
        $collection->allowed(['a', 'b']);
        $this->array($collection->allowed())->isEqualTo(['a', 'b']);

    }

    public function testOnlyAllowedKeysCanBeFilled()
    {
        $collection = new \ObjectivePHP\Primitives\Collection();

        $collection->allowed('allowed_key');
        $collection['allowed_key'] = 'string';
        $this->string($collection['allowed_key'])->isEqualTo('string');
        $this->exception(function () use($collection) {
            $collection['illegal_key'] = 'test';
        })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_ILLEGAL_KEY);

    }

    public function testOnlyAllowedKeysCanBeRead()
    {
        $collection = new \ObjectivePHP\Primitives\Collection();

        $collection->allowed('allowed_key');

        $this->variable($collection['allowed_key'])->isEqualTo(null);


        $this->exception(function () use($collection) {
            $collection['illegal_key'];
        })->isInstanceOf(Exception::class)->hasCode(Exception::COLLECTION_ILLEGAL_KEY);
    }

    public function testEachLoopWithCallback()
    {
        $collection = new \ObjectivePHP\Primitives\Collection([1, 2, 3]);

        $this
            ->object($collection->each(function(){}))
                ->isIdenticalTo($collection)
            ->array($collection->each(function(&$value) { $value *= 2;})->getArrayCopy())
                ->isEqualTo([2, 4, 6])

            ->exception(function () use($collection) {
                $collection->each('not callable');
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(\ObjectivePHP\Primitives\Exception::INVALID_CALLBACK);
    }

    public function testFilter()
    {
        $records = [1, false, null, ''];
        $collection = new \ObjectivePHP\Primitives\Collection($records);

        $this
            ->exception(function() use($collection){
                $collection->filter('exception');
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(\ObjectivePHP\Primitives\Exception::INVALID_CALLBACK);

        $this
            ->object($filtered = $collection->filter())
                ->isInstanceOf(get_class($collection))
                ->isNotIdenticalTo($collection)
            ->array($filtered->getArrayCopy())
                ->isEqualTo([1])

            ->object($collection->filter(true))
                ->isIdenticalTo($collection)
            ->array($collection->getArrayCopy())
                ->isEqualTo([1])
        ;

        $records = [1, 'test', 'test', ''];
        $collection = new \ObjectivePHP\Primitives\Collection($records);
        $this
            ->object($filtered = $collection->filter(function(){ return false; }))
                ->isInstanceOf(get_class($collection))
                ->isNotIdenticalTo($collection)
            ->array($filtered->getArrayCopy())
                ->isEqualTo([])

            ->object($filtered = $collection->filter(function(){ return false; }, true))
                ->isIdenticalTo($collection)
            ->array($filtered->getArrayCopy())
                ->isEqualTo([])
        ;
    }

}