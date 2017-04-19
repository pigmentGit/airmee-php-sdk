<?php

/**
 * @file DeliveryCannotBeRequestedException.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Exceptions;

/**
 * An exception raised by the server when a request for delivery schedules fails.  This is usually because Airmee
 * does not (and will not) deliver to the specified address.
 */
class DeliveryCannotBeRequestedException extends AirmeeException
{
}