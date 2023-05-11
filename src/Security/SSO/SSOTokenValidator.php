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
	
	protected $telaApiKey;
	protected $plantnetPullApiKey;

	public function __construct(string $annuaireBaseUrl, bool $ignoreSslIssues, string $telaApiKey, string $plantnetPullApiKey) {
	    $this->annuaireBaseUrl = $annuaireBaseUrl;
		$this->telaApiKey = $telaApiKey;
		$this->plantnetPullApiKey= $plantnetPullApiKey;
        if (empty($this->annuaireBaseUrl)) {
            throw new MisconfiguredSSOTokenValidatorException();
        }
		if (empty($this->telaApiKey) && empty($this->plantnetPullApiKey)) {
			throw new MisconfiguredAPIKeyTokenValidatorException();
		}
	    $this->ignoreSslIssues = $ignoreSslIssues ;
	}

	private function generateAuthCheckURL($token) {
		print_r('verification du jeton #-> ');
		$verificationServiceURL = $this->annuaireBaseUrl.':auth/verifierjeton';
		$verificationServiceURL .= "?token=" . $token;
//		print_r($verificationServiceURL);
        return $verificationServiceURL;
	}

	/**
	 * Verifies the authenticity of a token using the "annuaire" SSO service
	 */
	public function validateToken($token) {
		print_r('validate token in SSOTokenValidator#-> ');
//		print_r($token);
//		print_r(' #-> ');
//		if ($token == $this->telaApiKey || $token == $this->plantnetPullApiKey){
//			print_r('api key ok');
//		}
		
		$verificationServiceURL = $this->generateAuthCheckURL($token);
		$ch = curl_init();
		$timeout = 3;
		curl_setopt($ch, CURLOPT_URL, $verificationServiceURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		// equivalent of "-k", ignores SSL self-signed certificate issues
		// (for local testing only)
		if ($this->ignoreSslIssues) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

        $retry = 0;
        do {
            $data = curl_exec($ch);
            $retry++;
        } while (curl_errno($ch) === 28 && $retry <= 5);

        if ( curl_errno($ch) ) {
            throw new \Exception ('curl erreur: ' . curl_errno($ch));
        }
		curl_close($ch);
		$info = $data;

		$info = json_decode($info, true);
		return ($info === true);
	}
	
	public function validateAPIKey($token){
		if ($this->plantnetPullApiKey == $token || $this->telaApiKey == $token){
			return true;
		} else {
			return false;
		}
	}

}
