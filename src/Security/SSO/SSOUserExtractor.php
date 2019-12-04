<?php

namespace App\Security\SSO;

use App\Security\User\UnloggedAccessException;

use App\Security\SSO\SSOTokenValidator;
use App\Security\SSO\SSOTokenDecoder;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\HttpFoundation\Request;

/**
 * Authentication and user management using Tela Botanica's SSO
 *
 * @todo : param vide constructeur
 */
class SSOUserExtractor {

    // Name of the HTTP header containing the auth token:
    const TOKEN_HEADER_NAME             = "authorization";
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

    public function extractUser(Request $request) {
        $token = $this->extractTokenFromRequest($request);
        if ( null === $token) {
            throw new UnloggedAccessException('You must be logged into tela-botanica SSO system to access this part of the app.');
        }
        return $this->extractUserFromToken($token);
    }

    public function extractUserFromToken(string $token) {

        if (null == $token) {
            return null;
        }

        $tokenDecoder = new SSOTokenDecoder();
// die(var_dump($tokenDecoder)); 
        $userInfo = $tokenDecoder->getUserFromToken($token);
        //$role = new Role();
        $roles = array();
        if (in_array( 
            SSOUserExtractor::ADMIN_PERMISSION,
            $userInfo[SSOUserExtractor::PERMISSIONS_TOKEN_PROPERTY])) {
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
//die(var_dump($userInfo)); 
        $user = new TelaBotanicaUser(
            intval($userInfo['id']), $userInfo['sub'], $userInfo['prenom'], 
            $userInfo['nom'], $userInfo['pseudo'], 
            $userInfo['pseudoUtilise'], $userInfo['avatar'], 
            $roles, null, $token);
//die(var_dump($user));  
        // Returns the user, checkCredentials() is gonna be called
        return $user;
    }



    public function extractTokenFromRequest(Request $request) {
        $headers = $request->headers;
        if (null == $headers->get(SSOUserExtractor::TOKEN_HEADER_NAME)) {
            return null;
        }
        else {
            // We should get a header of value like "Bearer XXXXXXXXXXXX"
            // Let's explode it:
            $bits = explode(" ", $headers->get(SSOUserExtractor::TOKEN_HEADER_NAME));
            // The second part of the header value is the token value
            if (sizeof($bits) == 2) {           
                return  $bits[1];
            }
            // Malformed header value: return empty credientials:
            else {
                return null;
            }
        }
    }

    /**
     * Checks if the SSO JWT token is valid.
     *
     * Returns true if that's the case (which will cause authentication 
     * success), else false.
     */
    public function validateToken(string $token): bool {

        if (null === $token) {
            return false;
        }

        $tokenValidator = new SSOTokenValidator();

        return $tokenValidator->validateToken($token);
    }
}
