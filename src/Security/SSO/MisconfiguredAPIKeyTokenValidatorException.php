<?php

namespace App\Security\SSO;

use \Exception;


class MisconfiguredAPIKeyTokenValidatorException extends Exception {

	public function __construct() {
		parent::__construct("Misconfigured APIKEYTokenValidator : no API key provided to constructor.", 0, null);
	}

}
