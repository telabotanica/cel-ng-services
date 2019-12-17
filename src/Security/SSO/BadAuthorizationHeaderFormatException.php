<?php

namespace App\Security\SSO;

use App\Security\SSO\MisconfiguredSSOTokenValidatorException;

use Symfony\Component\Dotenv\Dotenv;

/**
 * Thrown when the "Authorization" header value format is not valid.
 */
class BadAuthorizationHeaderFormatException extends \Exception {

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }

}
