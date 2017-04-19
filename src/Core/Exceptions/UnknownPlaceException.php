<?php

/**
 * @file UnknownPlaceException.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Exceptions;

/**
 * An exception returned by the server when the client submits a request with a place_id which is not recognised.
 */
class UnknownPlaceException extends AirmeeException
{
}