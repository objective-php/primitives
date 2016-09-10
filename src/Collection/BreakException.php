<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Primitives\Collection;


use ObjectivePHP\Primitives\Exception;

/**
 * Class BreakException
 *
 * This exception is meant to break Collection::each() loop.
 * By throwing it from the callable passed to each(), the
 * iteration will be interrupted.
 *
 * @package ObjectivePHP\Primitives\Collection
 */
class BreakException extends Exception
{
    
}
