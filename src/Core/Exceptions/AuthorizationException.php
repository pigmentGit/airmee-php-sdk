<?php

/**
 * @file AuthorizationException.php
 * @copyright © 2017 Toptal LLC.  Used under license.
 * @author 2017 Melon Software.
 */

namespace Airmee\PhpSdk\Core\Exceptions;

/**
 * An exception returned by the server when the client has submitted an unauthenticated request, or the credentials
 * supplied do not give access to the requested resource.
 */
class AuthorizationException extends AirmeeException
{
}