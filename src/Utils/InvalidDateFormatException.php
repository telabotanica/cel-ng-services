<?php

namespace App\Utils;

use \Exception;

/**
 * Thrown when an invalid date has been provided during import.
 *
 * @package App\Utils
 */
// @refactor put this in root Exception package and extend BaseException.
class InvalidDateFormatException extends Exception {

	public function __construct(string $invalidDate) {
	    $message = sprintf('Invalid date format: %s', $invalidDate);

		parent::__construct($message, 0, null);
	}

}
