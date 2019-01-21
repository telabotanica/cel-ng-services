<?php

namespace App\Security\SSO;

use App\Security\SSO\MisconfiguredSSOTokenValidatorException;

/**
 * Authentication and user management using Tela Botanica's SSO
 * @todo : if annuaireURL null ou vide => return UnknownUser
 * @todo : param vide constructeur
 */
class SSOTokenValidator {

	/** The URL for the "annuaire" SSO Web Service */
	protected $annuaireURL;
	/** The URL for the "annuaire" SSO Web Service */
	protected $ignoreSSLIssues = false;


	public function __construct($annuaireURL, $ignoreSSLIssues) {
		$this->annuaireURL = $annuaireURL;
		// (for local testing only)
		if (! empty($ignoreSSLIssues) && $ignoreSSLIssues === true) {
			$this->ignoreSSLIssues = $ignoreSSLIssues;
		}
		
	}

	private function generateAuthCheckURL($token) {
		$verificationServiceURL = $this->annuaireURL;
		$verificationServiceURL = trim($verificationServiceURL, '/') . "/verifytoken";
		$verificationServiceURL .= "?token=" . $token;
                return $verificationServiceURL;
	}

	/**
	 * Verifies the authenticity of a token using the "annuaire" SSO service
	 */
	public function validateToken($token) {
		if ( empty($this->annuaireURL) ) {
			throw new MisconfiguredSSOTokenValidatorException();
		}
		$verificationServiceURL = $this->generateAuthCheckURL();
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $verificationServiceURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		// equivalent of "-k", ignores SSL self-signed certificate issues
		// (for local testing only)
		if (! empty($this->ignoreSSLIssues) && $this->ignoreSSLIssues === true) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		$data = curl_exec($ch);
		curl_close($ch);
		$info = $data;
		$info = json_decode($info, true);
		return ($info === true);
	}

}
