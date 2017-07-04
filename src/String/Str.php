<?php

namespace ObjectivePHP\Primitives\String;

use ObjectivePHP\Primitives\AbstractPrimitive;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Primitives\Exception;
use ObjectivePHP\Primitives\Number\Number;

class Str extends AbstractPrimitive
{
    
    const TYPE = 'string';
    
    const LEFT  = 1;
    const RIGHT = 2;
    const BOTH  = 4;
    
    const FROM_END = 8;
    
    const CASE_SENSITIVE = 16;
    const STRICT         = 32;
    
    const REGEXP = 64;
    const LIMIT  = 128;
    
    const UPPER_ALL   = 'all';
    const UPPER_FIRST = 'first';
    const UPPER_WORDS = 'words';
    
    /**
     * @var array
     */
    protected $variables = [];
    
    /**
     * @param string $string
     */
    public function __construct($string = '', $variables = [])
    {
        $this->setInternalValue($string);
        $this->setVariables($variables);
    }
    
    public function setVariables($variables)
    {
        $this->variables = $variables;
        
        return $this;
    }
    
    /**
     * @param $string
     *
     * @return Str
     */
    static public function cast($string)
    {
        if ($string instanceof Str) {
            return $string;
        }
        
        return new Str($string);
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
     * @param string $mode
     *
     * @return $this
     *
     * @throws Exception
     */
    public function upper($mode = self::UPPER_ALL)
    {
        
        switch ($mode) {
            case self::UPPER_FIRST:
                $upperValue = mb_strtoupper(mb_substr($this->getInternalValue(), 0,
                        1)) . mb_substr($this->getInternalValue(), 1);
                break;
            
            case self::UPPER_WORDS:
                $upperValue = $this->split('/\s+/', self::REGEXP)->each(function (&$word) {
                    /** @var String $word */
                    $word->upper(self::UPPER_FIRST);
                })->join()->getInternalValue()
                ;
                break;
            
            default:
            case self::UPPER_ALL:
                $upperValue = mb_strtoupper($this->getInternalValue(), 'UTF-8');
                break;
        }
        
        $this->setInternalValue($upperValue);
        
        return $this;
    }
    
    /**
     * Expose both explode() and preg_split functions
     *
     * @param string $separator
     * @param int    $flags self::REGEXP (+ native preg_split flags if any) to tell separator is PCRE
     * @param int    $limit limit results to $limit entries
     *
     * @return Collection
     * @throws Exception
     */
    public function split($separator = ',', $flags = 0, $limit = null)
    {
        if (!is_string($separator)) {
            throw new Exception('invalid pattern', Exception::INVALID_PARAMETER);
        }
        
        $limit = (($flags & self::LIMIT) && $limit) ? $limit : null;
        
        if ($flags & self::REGEXP) {
            $result = @preg_split($separator, $this->getInternalValue(), $limit, $flags);
            
            $error = preg_last_error();
            if ($result === false || $error) {
                switch ($error) {
                    case PREG_INTERNAL_ERROR:
                        $message = 'PREG engine internal error';
                        break;
                    
                    default:
                        $message = 'Unknown error when calling preg_split (' . $error . ')';
                        break;
                }
                
                throw new Exception($message, Exception::INVALID_REGEXP);
            }
            
        } else {
            $limit  = $limit ?: PHP_INT_MAX;
            $result = explode($separator, $this->getInternalValue(), $limit);
        }
        
        if ($result) {
            $result = array_map(function ($string) {
                return new Str($string);
            },
                $result);
            
            return (new Collection($result))->restrictTo(Str::class);
        } else {
            return (new Collection())->restrictTo(Str::class);
        }
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
     * @param string|String $string
     *
     * @return $this
     */
    public function prepend($string)
    {
        return (clone $this)->insert($string, 0);
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
        
        if (!is_int($position) || is_array($string)) {
            throw new Exception('invalid index or string', Exception::INVALID_PARAMETER);
        }
        
        
        $this->setInternalValue(substr($this->getInternalValue(), 0,
                $position) . (string)$string . substr($this->getInternalValue(), $position));
        
        return $this;
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
     * @param string|\ObjectivePHP\Primitives\String $string Needle
     * @param int                                    $offset Offset to start search
     * @param null                                   $flags
     *
     * @throws Exception
     *
     * @return boolean
     */
    public function locate($string, $offset = 0, $flags = null)
    {
        
        if (is_array($string) || !is_int($offset)) {
            throw new Exception('Invalid needle type', Exception::INVALID_PARAMETER);
        }
        
        $string = (string)$string;
        
        if ($flags & self::FROM_END) {
            $output = ($flags & self::CASE_SENSITIVE)
                ? strrpos($this->getInternalValue(), $string, $offset)
                : strripos($this->getInternalValue(), $string, $offset);
        } else {
            if ($offset < 0) {
                throw new Exception('Offset cannot be negative', Exception::INVALID_PARAMETER);
            }
            
            $output = ($flags & self::CASE_SENSITIVE) ? strpos($this->getInternalValue(), $string,
                $offset) : stripos($this->getInternalValue(), $string, $offset);
        }
        
        return $output;
    }
    
    /**
     * @param $pattern
     *
     * @return bool
     */
    public function matches($pattern)
    {
        return (bool)preg_match($pattern, $this->getInternalValue());
    }
    
    /**
     * @param null $charlist
     * @param null $ends
     *
     * @return $this
     */
    public function trim($charlist = null, $ends = null)
    {
        switch (true) {
            case is_null($charlist) && ($ends == self::BOTH || is_null($ends)):
                $this->setInternalValue(trim($this->getInternalValue()));
                break;
            
            case is_null($ends) || $ends == self::BOTH:
                $this->setInternalValue(trim($this->getInternalValue(), $charlist));
                break;
            
            case !is_null($ends):
                switch (true) {
                    case $ends == self::LEFT:
                        $callback = 'ltrim';
                        break;
                    
                    case $ends == self::RIGHT:
                        $callback = 'rtrim';
                        break;
                }
                
                if (is_null($charlist)) {
                    $this->setInternalValue(call_user_func($callback, $this->getInternalValue()));
                } else {
                    $this->setInternalValue(call_user_func($callback, $this->getInternalValue(), $charlist));
                }
                
                break;
            
        }
        
        return $this;
        
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
        
        if ($flags & self::REGEXP) {
            return $this->regexplace($pattern, $replacement);
        } else {
            $function = ($flags & self::CASE_SENSITIVE) ? 'str_replace' : 'str_ireplace';
            $result   = $function($pattern, $replacement, $this->getInternalValue());
            
        }
        $this->setInternalValue($result);
        
        return $this;
    }
    
    /**
     * Identical to self::replace() but using a regular expression as pattern
     *
     * @param $pattern
     * @param $replacement
     *
     * @return mixed
     */
    public function regexplace($pattern, $replacement)
    {
        $result = preg_replace($pattern, $replacement, $this->getInternalValue());
        $this->setInternalValue($result);
        
        return $this;
    }
    
    /**
     * Return a part of current string as new Str object
     *
     * @param      $start
     * @param null $length
     *
     * @return Str
     */
    public function extract($start, $length = null)
    {
        return $this->copy()->crop($start, $length);
    }
    
    /**
     * Crop the current internal value
     *
     * @param      $start
     * @param null $length
     *
     * @return $this
     */
    public function crop($start, $length = null)
    {
        $this->setInternalValue(mb_substr($this->getInternalValue(), $start, $length, 'UTF-8'));
        
        return $this;
    }
    
    /**
     * @param     $needle
     * @param int $flags
     *
     * @return bool
     */
    public function contains($needle, $flags = 0)
    {
        return ($flags & self::CASE_SENSITIVE) ? (strpos($this->getInternalValue(),
                $needle) !== false) : (stripos($this->getInternalValue(), $needle) !== false);
    }
    
    /**
     * Crypts internal value using PHP's native crypt() function
     *
     * @param null $salt
     *
     * @return $this
     */
    public function crypt($salt = null)
    {
        $this->setInternalValue(crypt($this->getInternalValue(), $salt));
        
        return $this;
    }
    
    /**
     * Challenge current (encrypted) value to input
     *
     * @param $secret
     *
     * @return bool
     */
    public function challenge($secret)
    {
        return $this->getInternalValue() == crypt($secret, $this->getInternalValue());
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->build();
    }
    
    public function build()
    {
        $builtString = $this->getInternalValue();
        
        if (is_null($builtString)) {
            return '';
        }
        
        if ($this->variables) {
            // first separate named and anonymous contents
            $named     = [];
            $anonymous = [];
            
            foreach ($this->variables as $key => $value) {
                if (is_int($key)) {
                    $anonymous[] = $value;
                } else {
                    $named[$key] = $value;
                }
            }
            
            // handle named placeholders
            foreach ($named as $placeholder => $value) {
                $builtString = str_replace(':' . $placeholder, (string)$value, $builtString);
            }
            
            // then anonymous ones, if any value provided
            if ($anonymous) {
                $builtString = vsprintf($builtString, $anonymous);
            }
        }
        
        return $builtString;
    }
    
    /**
     * Return a md5 representation of internal value
     *
     * Note that a native string is returned and not a Str primitive,
     * because md5 strings are not supposed to be manipulated
     *
     * @return string
     */
    public function md5()
    {
        return md5($this->value);
    }
    
    /**
     * Set named placeholder value
     *
     * @param $placeholder
     * @param $value
     *
     * @return $this
     */
    public function setVariable($placeholder, $value)
    {
        
        $this->variables[$placeholder] = $value;
        
        return $this;
    }
    
    /**
     * Set anonymous placeholder variable
     *
     * @param $value
     *
     * @return $this
     */
    public function addVariable($value)
    {
        $this->variables[] = $value;
        
        return $this;
    }
    
    /**
     * Clear placeholders values
     *
     * @return $this
     */
    public function clear()
    {
        $this->variables = [];
        
        return $this;
    }
    
    public function camelCase()
    {
        $string = $this->getInternalValue();
        
        $parts = explode('_', $string);
        $parts = array_map(function ($value) {
            return ucfirst(strtolower($value));
        }, $parts);
        
        $this->setInternalValue(lcfirst(implode('', $parts)));
        
        return $this;
    }
    
    public function snakeCase()
    {
        $input = $this->getInternalValue();
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        $this->setInternalValue(implode('_', $ret));
        
        return $this;
    }
}
