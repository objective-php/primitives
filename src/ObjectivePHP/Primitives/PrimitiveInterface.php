<?php

namespace ObjectivePHP\Primitives;

/**
 * Temp implementation proposition
 * JsonSerializable: each primitive must be serializable by json_encode()
 *
 * Class PrimitiveInterface
 * @package Phocus\Primitives
 */
interface PrimitiveInterface extends \JsonSerializable
{
    /**
     * Set the primitive object initial value
     *
     * @param $value
     *
     * @return $this
     */
    public function set($value);

    /**
     * Return the primitive value in its native representation
     *
     * @return mixed
     */
    public function get();

    /**
     * Apply a callback to primitive's internal value
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function apply(callable $callback);

}