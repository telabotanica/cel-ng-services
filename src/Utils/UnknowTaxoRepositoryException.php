<?php

namespace App\Utils;

use \Exception;

/**
 * Thrown when an taxo repo has been provided during import.
 *
 * @package App\Utils
 */
// @refactor put this in root Exception package and extend BaseException.
class UnknowTaxoRepositoryException extends Exception {

	public function __construct() {
		parent::__construct("Invalid value for taxonomic repository name.", 0, null);
	}

}
