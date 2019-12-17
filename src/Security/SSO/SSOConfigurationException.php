<?php

namespace App\Security\SSO;

use App\Exception\Cel2BaseException;

/**
 * Thrown when the SSO Web service URL is not configured in.
 */
final class SSOConfigurationException extends Cel2BaseException { }
