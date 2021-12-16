<?php

namespace App\Security\SSO;

/**
 * Authentication and user management using Tela Botanica's SSO
 *
 * @todo : param vide constructeur
 */
class SSOTokenValidator {

	/** The base URL for the "annuaire" SSO Web Service */
	protected $annuaireBaseUrl;

	protected $ignoreSslIssues = false;

	public function __construct(string $annuaireBaseUrl, bool $ignoreSslIssues) {
	    $this->annuaireBaseUrl = $annuaireBaseUrl;
        if (empty($this->annuaireBaseUrl)) {
            throw new MisconfiguredSSOTokenValidatorException();
        }
	    $this->ignoreSslIssues = $ignoreSslIssues ;
	}

	private function generateAuthCheckURL($token) {
		$verificationServiceURL = $this->annuaireBaseUrl.':auth/verifierjeton';
		$verificationServiceURL .= "?token=" . $token;

        return $verificationServiceURL;
	}

	/**
	 * Verifies the authenticity of a token using the "annuaire" SSO service
	 */
	public function validateToken($token) {
		$verificationServiceURL = $this->generateAuthCheckURL($token);
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $verificationServiceURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		// equivalent of "-k", ignores SSL self-signed certificate issues
		// (for local testing only)
		if ($this->ignoreSslIssues) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$data = curl_exec($ch);

        if ( curl_errno($ch) ) {
            throw new \Exception ('curl erreur: ' . curl_errno($ch));
        }
		curl_close($ch);
		$info = $data;

		$info = json_decode($info, true);
		return ($info === true);
	}

}
