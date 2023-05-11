<?php

namespace App\Security\SSO;

use App\Security\User\UnloggedAccessException;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\HttpFoundation\Request;

/**
 * Authentication and user management using Tela Botanica's SSO
 *
 * @todo : param vide constructeur
 */
class SSOUserExtractor {

    // Name of the HTTP header containing the auth token:
    const TOKEN_HEADER_NAME             = "Authorization";
    // Name of "permissions" property in the auth token:
    const PERMISSIONS_TOKEN_PROPERTY    = 'permissions';
    // Permission for "admin" (in the auth token):
    const ADMIN_PERMISSION              = 'administrator';
    // App "admin" role:
    const ADMIN_ROLE                    = 'ROLE_ADMIN';
    // App "admin" role name:
    const ADMIN_ROLE_NAME               = 'Admin';
    // App "user" role:
    const USER_ROLE                     = 'ROLE_USER';
    // App "user" role name:
    const USER_ROLE_NAME                = 'User';

    private $ssoTokenValidator;
    private $ssoTokenDecoder;

    public function __construct(SSOTokenValidator $ssoTokenValidator, SSOTokenDecoder $ssoTokenDecoder)
    {
        $this->ssoTokenValidator = $ssoTokenValidator;
        $this->ssoTokenDecoder = $ssoTokenDecoder;
    }

    public function extractUser(Request $request) {
        $token = $this->extractTokenFromRequest($request);
		print_r('extract user #-> ');
        if ( null === $token) {
            throw new UnloggedAccessException('You must be logged into tela-botanica SSO system to access this part of the app.');
        }
        return $this->extractUserFromToken($token);
    }

    public function extractUserFromToken(string $token) {
print_r('extract user from token #-> ');
        if (null == $token) {
            return null;
        }

		try {
			$userInfo = $this->ssoTokenDecoder->getUserFromToken($token);
			//$role = new Role();
			$roles = array();
			if (in_array(
				SSOUserExtractor::ADMIN_PERMISSION,
				$userInfo[SSOUserExtractor::PERMISSIONS_TOKEN_PROPERTY] ?? [])) {
				/*
				$role->setName(SSOAuthenticator::ADMIN_ROLE_NAME);
				$role->setRole(SSOAuthenticator::ADMIN_ROLE);
				*/
				$roles[] = SSOUserExtractor::ADMIN_ROLE;
			}
			else {
				
				/*
				$role->setName(SSOAuthenticator::USER_ROLE_NAME);
				$role->setRole(SSOAuthenticator::USER_ROLE);
				*/
				$roles[] = SSOUserExtractor::USER_ROLE;
			}

			$user = new TelaBotanicaUser(
				intval($userInfo['id']), $userInfo['sub'], $userInfo['prenom'],
				$userInfo['nom'], $userInfo['pseudo'],
				$userInfo['pseudoUtilise'], $userInfo['avatar'] ?? '',
				$roles, null, $token);

			// Returns the user, checkCredentials() is gonna be called
		} catch (\Exception $exception){
			$user = $user = new TelaBotanicaUser(
				69387, '', '',
				'', '',
				'', '',
				['ROLE_API'], null, $token);
		}
//        dd($user);
        return $user;
    }



    public function extractTokenFromRequest(Request $request) {
		print_r('extract token from request in SSOUserExtractor #-> ');
        return $request->headers->get(SSOUserExtractor::TOKEN_HEADER_NAME);
    }

    /**
     * Checks if the SSO JWT token is valid.
     *
     * Returns true if that's the case (which will cause authentication 
     * success), else false.
     */
    public function validateToken(string $token): bool {
print_r('validate token in SSOUserExtractor#-> ');
        if (null === $token) {
            return false;
        }
		
		if ($this->ssoTokenValidator->validateToken($token)){
			return true;
		} else {
			return $this->ssoTokenValidator->validateAPIKey($token);
		}

//		dd($token);
//		return true;
//dd($this->ssoTokenValidator->validateToken($token));
//        return $this->ssoTokenValidator->validateToken($token);
    }
}
