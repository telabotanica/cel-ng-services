<?php

namespace App\Exception;

/**
 * Base Exception class.
 */
class Cel2BaseException extends \Exception {

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }

}


