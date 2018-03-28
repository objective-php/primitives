<?php

namespace Tests\ObjectivePHP\Primitives\Collection;

use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Primitives\Exception\BreakException;
use ObjectivePHP\Primitives\Exception\CollectionException;
use ObjectivePHP\Primitives\Exception\UnsupportedTypeException;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionTest
 *
 * @package Tests\ObjectivePHP\Primitives
 */
class CollectionTest extends TestCase
{
    public function testRestrictTo()
    {
        $collection = (new Collection)->restrictTo(Collection::class);
        $otherCollection = new Collection;

        $collection[] = $otherCollection;

        $this->assertEquals([new Collection()], $collection->getInternalValue());

        $this->expectException(UnsupportedTypeException::class);
        $this->expectExceptionMessage(
            'The value of type "string" doesn\'t match type restriction' .
            ' "ObjectivePHP\Primitives\Collection\Collection"'
        );

        $collection[] = 'this is not a Collection object';
    }

    public function testRestrictToCouldNotBeSetOnNonEmptyCollection()
    {
        $collection = new Collection(['test']);

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('Type restriction could not be applied to a non empty collection');

        $collection->restrictTo(Collection::class);
    }

    /**
     * @dataProvider dataProviderForTestTypeValidity
     *
     * @param $type
     * @param $valid
     */
    public function testRestrictToWithVariousRestrictions($type, $valid)
    {
        $collection = new Collection();

        if (!is_null($valid)) {
            $collection->restrictTo($type);
            $this->assertEquals($valid, $collection->getType());
        }
    }

    public function dataProviderForTestTypeValidity()
    {
        return
            [
                [\RecursiveDirectoryIterator::class, \RecursiveDirectoryIterator::class],
                [\ArrayAccess::class, \ArrayAccess::class],
                [false, false],
            ];
    }

    public function testRestrictToInterface()
    {
        $collection = (new Collection())->restrictTo(TestInterface::class);

        $this->assertEquals(TestInterface::class, $collection->getType());
    }

    public function testEach()
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertSame($collection, $collection->each(function () {
        }));

        $this->assertEquals([2, 4, 6], $collection->each(function (&$value) {
            $value *= 2;
        })->toArray());
    }

    public function testBreakingEachLoop()
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertEquals([2, 4, 3], $collection->each(function (&$value) {
            if ($value == 3) {
                throw new BreakException();
            }

            $value *= 2;
        })->toArray());
    }

    public function testFilter()
    {
        $records = [1, false, null, ''];
        $collection = new Collection($records);

        // default behaviour: filter returns a new Collection
        $filtered = $collection->filter();
        $this->assertInstanceOf(Collection::class, $filtered);
        $this->assertNotSame($collection, $filtered);
        $this->assertEquals([1], $filtered->toArray());

        // alternative: it returns self
        $filtered = $collection->copy()->filter();
        $this->assertInstanceOf(Collection::class, $filtered);
        $this->assertNotSame($collection, $filtered);
        $this->assertEquals([1], $filtered->toArray());


        // other scenario
        $records = [1, 'test', 'test', ''];
        $collection = new Collection($records);
        $filtered = $collection->filter(function () {
            return false;
        });
        $this->assertInstanceOf(Collection::class, $filtered);
        $this->assertNotSame($collection, $filtered);
        $this->assertEquals([], $filtered->toArray());


        $filtered = $collection->filter(function () {
            return false;
        }, true);
        $this->assertNotSame($collection, $filtered);
        $this->assertEquals([], $filtered->toArray());
    }

    public function testJoin()
    {
        $collection = (new Collection(['Objective', 'PHP']));

        $this->assertEquals('Objective PHP', $collection->join()->getInternalValue());
    }

    public function testFlip()
    {
        $data = ['a' => 'w', 'b' => 'x', 'y' => null, 'z' => ''];


        $collection = (new Collection($data))->flip();

        $this->assertEquals(['w' => 'a', 'x' => 'b', 0 => 'y', 1 => 'z'], $collection->toArray());
    }

    public function testAppend()
    {
        $collection = new Collection();

        $collection->append('value1');
        $this->assertEquals(['value1'], $collection->toArray());
        $collection->append('value2', 'value3');
        $this->assertEquals(['value1', 'value2', 'value3'], $collection->toArray());


        $collection = new Collection();
        $result = $collection->append('test');
        $this->assertSame($collection, $result);
    }

    public function testPrepend()
    {
        $collection = new Collection();

        $collection->prepend('value1');
        $this->assertEquals(['value1'], $collection->toArray());
        $collection->prepend('value2');
        $this->assertEquals(['value2', 'value1'], $collection->toArray());
        $collection->prepend('value3', 'value4');
        $this->assertEquals(['value3', 'value4', 'value2', 'value1'], $collection->toArray());


        $collection = new Collection();
        $result = $collection->prepend('test');
        $this->assertSame($collection, $result);
    }

    public function testRename()
    {
        $collection = new Collection(['A' => 'a', 'B' => 'b']);

        $collection = $collection->rename('A', 'C');
        $this->assertEquals(['C' => 'a', 'B' => 'b'], $collection->toArray());

        $collection = $collection->rename('B', 'D');
        $this->assertEquals(['C' => 'a', 'D' => 'b'], $collection->toArray());

        $this->expectException(CollectionException::class);
        $this->expectExceptionMessage('The key "Ham" was not found.');

        $collection->rename('Ham', 'Chicken');
    }

    public function testCastWithAnArray()
    {
        $value = ['a', 'b', 'c'];
        $castedCollection = Collection::cast($value);

        $this->assertInstanceOf(Collection::class, $castedCollection);
        $this->assertEquals($value, $castedCollection->getInternalValue());
        $this->assertSame($castedCollection, Collection::cast($castedCollection));
    }

    public function testCastWithAnArrayObject()
    {
        $value = new \ArrayObject(['a', 'b', 'c']);
        $castedCollection = Collection::cast($value);
        $this->assertInstanceOf(Collection::class, $castedCollection);
        $this->assertEquals($value->getArrayCopy(), $castedCollection->getInternalValue());
        $this->assertSame($castedCollection, Collection::cast($castedCollection));
    }

    public function testMerge()
    {
        $data = ['b' => 'y'];
        $collection = new Collection(['a' => 'x']);

        $collection = $collection->merge($data);
        $this->assertEquals(new Collection(['a' => 'x', 'b' => 'y']), $collection);

        $collection = $collection->merge(['a' => 'z']);
        $this->assertEquals(new Collection(['a' => 'z', 'b' => 'y']), $collection);
    }

    public function testUnion()
    {
        $collection = new Collection(['a' => 'x']);
        $collection = $collection->union(['a' => 'ignored', 'b' => 'y']);
        $this->assertEquals(new Collection(['a' => 'x', 'b' => 'y']), $collection);
    }

    public function testValues()
    {
        $collection = new Collection(['a' => 'x']);

        $values = $collection->values();

        $this->assertEquals(new Collection([0 => 'x']), $values);
    }

    public function testKeys()
    {
        $collection = new Collection(['a' => 'x']);

        $values = $collection->keys();

        $this->assertEquals(new Collection([0 => 'a']), $values);
    }

    public function testKeyIsEmpty()
    {
        $collection = new Collection([1 => 'test2', 0 => 'test']);
        $this->assertEquals(new Collection([0 => 1, 1 => 0]), $collection->keys());

        $collection = new Collection(['' => 'test']);
        $value = $collection->keys()[0];
        $this->assertTrue('' === $value);

        $collection = new Collection(['test']);
        $value = $collection->keys()->toArray()[0];
        $this->assertTrue(0 === $value);
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

    public function testHas()
    {
        $collection = new Collection(['a' => 'x', 'c' => 0, 'd' => null]);
        $this->assertTrue($collection->has('a'));
        $this->assertFalse($collection->has('b'));
        $this->assertTrue($collection->has('c'));
        $this->assertTrue($collection->has('d'));
    }

    public function testLacks()
    {
        $collection = new Collection(['a' => 'x']);
        $this->assertFalse($collection->lacks('a'));
        $this->assertTrue($collection->lacks('b'));
    }

    public function testSearch()
    {
        $collection = new Collection(['a' => 'x', 'b' => 'Y']);
        $this->assertEquals('a', $collection->search('x'));
        $this->assertEquals('a', $collection->search('X'));
        $this->assertEquals(null, $collection->search('X', true));
        $this->assertEquals('b', $collection->search('y'));
        $this->assertEquals(null, $collection->search('y', true));
    }

    public function testContains()
    {
        $collection = new Collection(['a' => 'x', 'b' => 'Y']);
        $this->assertTrue($collection->contains('x'));
        $this->assertTrue($collection->contains('X'));
        $this->assertFalse($collection->contains('X', true));
        $this->assertTrue($collection->contains('y'));
        $this->assertFalse($collection->contains('y', true));
    }

    public function testFirst()
    {
        $collection = new Collection(['a' => 'x', 'b' => 'Y']);

        $this->assertEquals('x', $collection->first());
    }

    public function testLast()
    {
        $collection = new Collection(['a' => 'x', 'b' => 'Y']);

        $this->assertEquals('Y', $collection->last());
    }

    public function testUnset()
    {
        $collection = new Collection(['a' => 'x', 'b' => 'Y']);

        unset($collection['a']);

        $this->assertEquals(new Collection(['b' => 'Y']), $collection);

        $collection->delete('b');
        $this->assertEquals([], $collection->toArray());
    }

    public function testRemove()
    {
        $collection = new Collection(['a' => 'y', 'b' => 'Y', 'c' => 'y']);

        $collection->remove('y', true);
        $this->assertEquals(['b' => 'Y'], $collection->toArray());

        $collection->remove('this should not have any effect');
        $collection->remove('y');
        $this->assertEquals([], $collection->toArray());
    }

    public function testHasReturnsTrueWhenValueIsNull()
    {
        $collection = new Collection(['a' => null]);

        $this->assertTrue($collection->offsetExists('a'));
    }

    public function testHasReturnsFalseWhenInternalValueIsNull()
    {
        $collection = new Collection();

        $this->assertFalse($collection->offsetExists('a'));
    }
}

/*********************
 * HELPERS
 ********************/
interface TestInterface
{
}
