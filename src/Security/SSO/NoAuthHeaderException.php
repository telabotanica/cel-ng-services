<?php

namespace App\Security\SSO;

use App\Exception\Cel2BaseException;

/**
 * Thrown when no "Authorization" header is available in the incoming request.
 */
final class NoAuthHeaderException extends Cel2BaseException { }
