<?php

namespace ObjectivePHP\Primitives\Exception;

/**
 * Class BreakException
 *
 * This exception is meant to break Collection::each() loop.
 * By throwing it from the callable passed to each(), the
 * iteration will be interrupted.
 *
 * @package ObjectivePHP\Primitives\Collection
 */
class BreakException extends CollectionException
{
}
