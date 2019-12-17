<?php

namespace App\Security\SSO;

use App\Exception\Cel2BaseException;

/**
 * Thrown when a request to the SSO validation Web service led to an error.
 */
final class SSOValidationException extends Cel2BaseException {

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }

}
