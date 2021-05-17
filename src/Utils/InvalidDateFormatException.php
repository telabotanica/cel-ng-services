<?php

namespace App\Utils;

use \Exception;


class InvalidDateFormatException extends Exception {

	public function __construct(string $invalidDate) {
	    $message = sprintf('Invalid date format: %s', $invalidDate);

		parent::__construct($message, 0, null);
	}

}
