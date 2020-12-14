<?php

namespace App\Security\SSO;

use App\Security\SSO\SSOConfigurationException;
use App\Security\SSO\SSOValidationException;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Simple tela annuaire SSO validation Web service client.
 */
class SSOTokenValidator {

	/** The URL for the "annuaire" SSO Web Service */
	protected $annuaireURL;
	/** The URL for the "annuaire" SSO Web Service */
	protected $ignoreSSLIssues = false;
	/** The timeout to be applied when requesting SSO Web service */
	protected $timeout = 5;
    private const BADLY_CONFIGURED_SSO_URL_MSG = 'The Web service app is badly ' . 
        'configured. SSO base Web service URL is empty.';
    private const SSO_VALIDATION_REQUEST_ERROR_MSG = 'An error occurred while ' . 
        'requesting telabotaica SSO validation Web service. The curl error' . 
        'nbr is: ';

	public function __construct(ParameterBagInterface $params) {
	    $this->annuaireURL = $params->get('sso.annuaire.url');
        if ( empty($this->annuaireURL) ) {
            throw new SSOConfigurationException(
                SSOTokenValidator::BADLY_CONFIGURED_SSO_URL_MSG);
        }
        $this->ignoreSSLIssues = $params->get('ignore.ssl.issues');
    }

	private function generateAuthCheckURL($token) {
		$verificationServiceURL = $this->annuaireURL;
		$verificationServiceURL = trim($verificationServiceURL, '/') . "/verifytoken";
		$verificationServiceURL .= "?token=" . $token;

        return $verificationServiceURL;
	}

	/**
	 * Verifies the authenticity of provided token (i.e. validates) against the
     * "annuaire" SSO service.
     *
     * Returns true if the token is valid (which will cause authentication 
     *         success), else false.
     */
	public function validateToken(string $token) {
		$verificationServiceURL = $this->generateAuthCheckURL($token);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $verificationServiceURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		// equivalent of "-k", ignores SSL self-signed certificate issues
		// (for local testing only)
		if (! empty($this->ignoreSSLIssues) && 
            $this->ignoreSSLIssues === true) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$data = curl_exec($ch);

        if ( curl_errno($ch) ) {
            throw new SSOValidationException(
                SSOTokenValidator::SSO_VALIDATION_REQUEST_ERROR_MSG . 
                curl_errno($ch));
        }
		curl_close($ch);
		$info = $data;

		$info = json_decode($info, true);
		return ($info === true);
	}

}
