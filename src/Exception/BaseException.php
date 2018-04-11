<?php

namespace ObjectivePHP\Primitives\Exception;

/**
 * Class BaseException
 * @package ObjectivePHP\Primitives\Exception
 */
class BaseException extends \Exception
{
    /**
     * @var \Exception
     */
    protected $previous;

    /**
     * Set the BaseException's Previous.
     *
     * @param \Exception $previous
     * @return $this
     */
    public function setPrevious(\Exception $previous)
    {
        $this->previous = $previous;
        return $this;
    }

    /**
     * Set the BaseException's Message.
     *
     * @param mixed $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the BaseException's Code.
     *
     * @param mixed $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
