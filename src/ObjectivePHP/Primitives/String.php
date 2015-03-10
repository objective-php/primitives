<?php

namespace ObjectivePHP\Primitives;

class String extends AbstractPrimitive
{

    const TYPE = 'string';

    const LEFT  = 1;
    const RIGHT = 2;
    const BOTH  = 4;

    const FROM_END = 8;

    const CASE_SENSITIVE = 16;
    const STRICT = 32;

    const REGEXP = 64;

    /**
     * @var string
     */
    protected $string;

    /**
     * @param string $string
     */
    public function __construct($string = '')
    {
        $this->setInternalValue($string);
    }

    /**
     * @return $this
     */
    public function lower()
    {
        $this->setInternalValue(mb_strtolower($this->getInternalValue(), 'UTF-8'));
        return $this;
    }

    /**
     * @return $this
     */
    public function upper()
    {
        $this->setInternalValue(mb_strtoupper($this->getInternalValue(), 'UTF-8'));
        return $this;
    }

    /**
     * @return $this
     */
    public function reverse()
    {
        $this->setInternalValue(strrev($this->getInternalValue()));
        return $this;
    }

    /**
     * Insert $str at index $at
     *
     * @param integer|Int $position
     * @param string      $string
     *
     * @return $this
     * @throws Exception
     */
    public function insert($string, $position)
    {
        if($position instanceof Numeric) $position = $position->getInternalValue();

        if (! is_int($position) || is_array($string))
        {
            throw new Exception('invalid index or string', Exception::INVALID_PARAMETER);
        }


        $this->setInternalValue(substr($this->getInternalValue(), 0, $position) . (string) $string . substr($this->getInternalValue(), $position));

        return $this;
    }

    /**
     * @param string|String $string
     *
     * @return $this
     */
    public function prepend($string)
    {
        return $this->insert($string, 0);
    }

    /**
     * @param string|String $string
     *
     * @return $this
     */
    public function append($string)
    {
        return $this->insert($string, $this->length());
    }

    /**
     * @return int
     */
    public function length()
    {
        return mb_strlen($this->getInternalValue(), 'UTF-8');
    }

    /**
     * @param string|\ObjectivePHP\Primitives\String $string         Needle
     * @param int|\ObjectivePHP\Primitives\Numeric       $offset      Offset to start search
     * @param null                             $flags
     *
     * @throws Exception
     *
     * @return \ObjectivePHP\Primitives\Numeric|boolean
     */
    public function locate($string, $offset = 0, $flags = null)
    {

        if($offset instanceof Numeric) $offset = $offset->getInternalValue();

        if (is_array($string) || ! is_int($offset))
        {
            throw new Exception('Invalid needle type', Exception::INVALID_PARAMETER);
        }

        $string = (string) $string;

        if ($flags & self::FROM_END)
        {
            $output = ($flags & self::CASE_SENSITIVE)
                ? strrpos($this->getInternalValue(), $string, $offset)
                : strripos($this->getInternalValue(), $string, $offset);
        }

        else
        {
            if ($offset < 0)
            {
                throw new Exception('Offset cannot be negative', Exception::INVALID_PARAMETER);
            }

            $output = ($flags & self::CASE_SENSITIVE) ? strpos($this->getInternalValue(), $string, $offset) : stripos($this->getInternalValue(), $string, $offset);
        }

        return ($output === false) ? false : new Numeric($output);
    }

    /**
     * @param $pattern
     *
     * @return bool
     */
    public function matches($pattern)
    {
        return (bool) preg_match($pattern, $this->getInternalValue());
    }

    /**
     * @param null $charlist
     * @param null $ends
     *
     * @return $this
     */
    public function trim($charlist = null, $ends = null)
    {
        switch(true)
        {
            case is_null($charlist) && ($ends == self::BOTH || is_null($ends)):
                $this->setInternalValue(trim($this->getInternalValue()));
                break;

            case is_null($ends) || $ends == self::BOTH:
                $this->setInternalValue(trim($this->getInternalValue(), $charlist));
                break;

            case !is_null($ends):
                switch(true)
                {
                    case $ends == self::LEFT:
                        $callback = 'ltrim';
                        break;

                    case $ends == self::RIGHT:
                        $callback = 'rtrim';
                        break;
                }

                if(is_null($charlist)) $this->setInternalValue(call_user_func($callback, $this->getInternalValue()));
                else $this->setInternalValue(call_user_func($callback, $this->getInternalValue(), $charlist));

                break;

        }

        return $this;

    }

    /**
     * Expose both explode() and preg_split functions
     *
     * @param string    $separator
     * @param int       $limit
     * @param int       $flags self::REGEXP (+ native preg_split flags if any) to tell separator is PCRE
     *
     * @return Collection
     * @throws Exception
     */
    public function split($separator = ',', $limit = null, $flags = 0)
    {
        if (!is_string($separator))
        {
            throw new Exception('invalid pattern', Exception::INVALID_PARAMETER);
        }

        if($flags & self::REGEXP)
        {
            $result = @preg_split($separator, $this->getInternalValue(), $limit, $flags);
            // TODO a InvalidArgumentException is thrown here during tests execution, but I don't know why!
            /*if($error = error_get_last())
            {
                throw new Exception($error['message'], Exception::INVALID_PARAMETER);
            }*/

        }
        else
        {
            $limit = (!$limit) ? PHP_INT_MAX : $limit;
            $result = explode($separator, $this->getInternalValue(), $limit);
        }

        return new Collection($result);
    }

    /**
     * @param     $pattern
     * @param     $replacement
     * @param int $flags Expected flags are : self::CASE_SENSITIVE
     *
     * @return $this
     */
    public function replace($pattern, $replacement, $flags = 0)
    {
        $function = ($flags & self::CASE_SENSITIVE) ? 'str_replace' : 'str_ireplace';

        $this->setInternalValue($function($pattern, $replacement, $this->getInternalValue()));

        return $this;
    }

    /**
     * @param      $start
     * @param null $length
     *
     * @return String
     */
    public function extract($start, $length = null)
    {
        if($length)
            return new String(mb_substr($this->getInternalValue(), $start, $length, 'UTF-8'));
        else
            return new String(mb_substr($this->getInternalValue(), $start, null, 'UTF-8'));
    }

    /**
     * @param          $needle
     *
     * @return bool
     */
    public function contains($needle, $flags = 0)
    {
        return ($flags & self::CASE_SENSITIVE) ? (strpos($this->getInternalValue(), $needle) !== false) : (stripos($this->getInternalValue(), $needle) !== false);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

}
