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

	public function __construct() {
		parent::__construct("Invalid date format.", 0, null);
	}

}
