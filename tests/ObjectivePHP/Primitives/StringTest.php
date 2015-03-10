<?php

namespace ObjectivePHP\Primitives\tests\units;

use mageekguy\atoum;
use ObjectivePHP\Primitives\Collection as ActualCollection;
use ObjectivePHP\Primitives\Exception;
use ObjectivePHP\Primitives\Numeric as ActualInt;
use ObjectivePHP\Primitives\String as ActualString;

class String extends atoum\test
{

    public function testAccessor()
    {
        $string = new ActualString('example string');
        $this->string($string->getInternalValue())->isEqualTo('example string');
    }

    public function testLowercase()
    {
        $string = new ActualString('TEST STRING');
        $this->string((string) $string->lower())->isEqualTo('test string');

        // with accented charcaters
        $string = new ActualString('CHAÎNE ACCENTUÉE');
        $this->string((string) $string->lower())->isEqualTo('chaîne accentuée');
    }

    public function testUppercase()
    {
        $string = new ActualString('test string');
        $this->string((string)$string->upper())->isEqualTo('TEST STRING');

        // with accented charcaters
        $string = new ActualString('chaîne accentuée');
        $this->string((string)$string->upper())->isEqualTo('CHAÎNE ACCENTUÉE');
    }

    public function testLength()
    {
        $string = new ActualString('test string');
        $this->integer((int) $string->length())->isEqualTo(11);

        // with accented charcaters
        $string = new ActualString('chaîne accentuée');
        $this->integer($string->length())->isEqualTo(16);

    }

    public function testMatches($subject, $pattern, $result)
    {
        $string = new ActualString($subject);

        $this->boolean($string->matches($pattern))->isEqualTo($result);
    }

    public function testExplode($separator, $limit, $string, $expected)
    {
        $string = new ActualString($string);

        if($expected instanceof \Exception)
        {
            $this->exception(function() use ($string, $separator) {
                $string->split($separator);
            })->isInstanceOf(get_class($expected))->hasCode($expected->getCode());
        }
        else
        {
            $this->object($collection = $string->split($separator, $limit))->isInstanceOf(ActualCollection::class)->isEqualTo($expected);
        }

    }

    protected function testExplodeDataProvider()
    {
        return
        [
            [', ', null, 'a, b, c, d, e', new ActualCollection(['a', 'b', 'c', 'd', 'e'])],
            ['/', null, 'a, b, c, d, e', new ActualCollection(['a, b, c, d, e'])],
            ['/*/', null, 'a/*/b', new ActualCollection(['a', 'b'])],
            [',', 2, 'a,b,c,d', new ActualCollection(['a', 'b,c,d'])],
            [',', -4, 'a,b,c,d', new ActualCollection([])],
            [['invalid separator'], null, '', new Exception(null, Exception::INVALID_PARAMETER)]
        ];
    }

    protected function testMatchesDataProvider()
    {
        return
        [
            ['hello world', '/world/', true],
            ['hello world', '/WORLD/', false],
            ['hello world', '/WORLD/i', true],
        ];
    }

    public function testTrim($string, $charlist, $ends, $expected)
    {
        $string = new ActualString($string);
        $result = $string->trim($charlist, $ends);

        $this->string((string) $result)->isEqualTo($expected);
    }

    protected function testTrimDataProvider()
    {
        return
        [
            [' test string ', null, null, 'test string'],
            [' test string ', null, \ObjectivePHP\Primitives\String::LEFT , 'test string '],
            [' test string ', null, \ObjectivePHP\Primitives\String::RIGHT , ' test string'],
            [' test string ', null, \ObjectivePHP\Primitives\String::BOTH , 'test string'],
            ['test string', 'tg', \ObjectivePHP\Primitives\String::LEFT , 'est string'],
            ['test string', 'tg', \ObjectivePHP\Primitives\String::RIGHT , 'test strin'],
            ['test string', 'tg', \ObjectivePHP\Primitives\String::BOTH , 'est strin'],
            ['test string', 'tg', null , 'est strin'],
        ];
    }

    public function testReplace()
    {
        $string = new ActualString('abcde');
        $this
            ->string((string) $string->replace('de', '_DE_de'))
                ->isEqualTo('abc_DE_de')
            ->string((string) $string->replace('_DE', '', ActualString::CASE_SENSITIVE))
                ->isEqualTo('abc_de');
    }

    public function testExtract()
    {
        $string = new ActualString('abcdefgh');

        $this->object($sub = $string->extract(1))->isInstanceOf(ActualString::class);
        $this->string((string) $sub)->isEqualTo('bcdefgh');

        $this->object($sub = $string->extract(1, 1))->isInstanceOf(ActualString::class);
        $this->string((string) $sub)->isEqualTo('b');

        // with accented charcaters
        $string = new ActualString('chaîne accentuée');
        $this->object($sub = $string->extract(3, 1))->isInstanceOf(ActualString::class);
        $this->string((string) $sub)->isEqualTo('î');

    }

    public function testContains()
    {
        $string = new ActualString("Hello World");

        $this
            ->boolean($string->contains('World'))
                ->isTrue()
            ->boolean($string->contains('foo'))
                ->isFalse()
            ->boolean($string->contains('world', ActualString::CASE_SENSITIVE))
                ->isFalse()
            ->boolean($string->contains('world'))
                ->isTrue()
            ->boolean($string->contains(1))
                ->isFalse();
        ;
    }

    public function testSplit($str, $pattern, $expected, $exception, $code)
    {
        $string = new ActualString($str);

        if ($exception)
        {
            $this
                ->exception(function() use($string, $pattern) {
                    $string->split($pattern, null, ActualString::REGEXP);
                })
                ->isInstanceOf($exception)
                ->hasCode($code)
            ;
        }
        else
        {
            $this
                ->object($col = $string->split($pattern, null, ActualString::REGEXP))
                ->isInstanceOf(ActualCollection::class)
                ->array($arr = $col->getArrayCopy())
                ->isEqualTo($expected);
            ;
        }
    }

    protected function testSplitDataProvider()
    {
        return
            [
                ['Hello,World', '/,/', ['Hello', 'World'], null, null],
                ['Hello,World', '/[\,]/', ['Hello', 'World'], null, null],
                ['Hello`World', '/`/', ['Hello', 'World'], null, null],
                ['Hello,World', ['this is no a string'],  null, Exception::class, Exception::INVALID_PARAMETER],
                ['Hello,World', 'this is not a valid regexp pattern',  null, \InvalidArgumentException::class, 0],
            ];
    }

    public function testInsert()
    {
        $string = new ActualString('Keep Phocus');

        $this
            ->exception(function()use($string){
                $string->insert('string', '30');
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER)
        ;

        $this
            ->exception(function()use($string){
                $string->insert([], []);
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER)
        ;

        $this
            ->exception(function()use($string){
                $string->insert(['array'], 30);
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER)
        ;

        $string = new ActualString('Objective');
        $this
            ->object($extendedString = $string->insert('Keep', 0))
                ->isInstanceOf(ActualString::class)
            ->string($extendedString->getInternalValue())
                ->isEqualTo('KeepObjective');

        $string = (new ActualString('Keep'))->insert('Objective', 99);
        $this
            ->string($string->getInternalValue())
            ->isEqualTo('KeepObjective');

        $string = (new ActualString('Keep'))->insert(new ActualString('Objective'), -2);
        $this
            ->string($string->getInternalValue())
            ->isEqualTo('KeObjectiveep');

        $string = (new ActualString('Keep'))->insert('Objective', 3);
        $this
            ->string($string->getInternalValue())
            ->isEqualTo('KeeObjectivep');
    }

    public function testPrepend()
    {
        $string = new ActualString('Phocus');
        $this
            ->string($string->prepend('Keep')->getInternalValue())
            ->isEqualTo('KeepPhocus');
    }

    public function testAppend()
    {
        $string = new ActualString('Keep');
        $this
            ->string($string->append('Phocus')->getInternalValue())
            ->isEqualTo('KeepPhocus');
    }

    public function testReverse()
    {
        $string = new ActualString('abc');
        $this
            ->string($string->reverse()->getInternalValue())
            ->isEqualTo('cba');
    }

    public function testLocate()
    {
        $string = new ActualString('Hello Php World');
        $this
            ->boolean($string->locate('AA'))
            ->isFalse()

            ->isFalse($string->locate('L', 0, ActualString::CASE_SENSITIVE))
            ->isEqualTo(false)

            ->integer($string->locate('W')->getInternalValue())
            ->isEqualTo(10)

            ->integer($string->locate('l', new ActualInt(5))->getInternalValue())
            ->isEqualTo(13)

            ->exception(function()use($string){
                $string->locate([]);
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER)

            ->exception(function()use($string){
                $string->locate('ss', 'aa');
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER)

            ->exception(function()use($string){
                $string->locate('test', -2);
            })
            ->isInstanceOf(Exception::class)
            ->hasCode(Exception::INVALID_PARAMETER)
            ->integer($string->locate('l', 0, ActualString::FROM_END)->getInternalValue())->isEqualTo(13)
            ->integer($string->locate('l', 0, ActualString::FROM_END)->getInternalValue())->isEqualTo(13)
            ->integer($string->locate('P', 0, ActualString::FROM_END)->getInternalValue())->isEqualTo(8)
            ->integer($string->locate('P', 0, ActualString::FROM_END | ActualString::CASE_SENSITIVE)->getInternalValue())->isEqualTo(6)
        ;
    }

}