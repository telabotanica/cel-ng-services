<?php

namespace App\Exception;

/**
 * Thrown when a file is not a valid image.
 */
class Cel2BaseException extends \Exception {

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }

}


