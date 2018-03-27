<?php

namespace ObjectivePHP\Primitives;

/**
 * Interface PrimitiveInterface
 *
 * JsonSerializable: each primitive must be serializable by json_encode()
 *
 * @package ObjectivePHP\Primitives
 */
interface PrimitiveInterface extends \JsonSerializable
{
    /**
     * Set the primitive object initial value
     *
     * @param mixed $value
     */
    public function setInternalValue($value);

    /**
     * Return the primitive value in its native representation
     *
     * @return mixed
     */
    public function getInternalValue();

    /**
     * Return a cloned primitive
     *
     * @return PrimitiveInterface
     */
    public function copy();

    /**
     * Convert a value to Primitive
     *
     * @param mixed $value
     *
     * @return PrimitiveInterface
     */
    public static function cast($value);
}
