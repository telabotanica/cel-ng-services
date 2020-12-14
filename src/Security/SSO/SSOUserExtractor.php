<?php

namespace App\Security\SSO;

use App\Security\User\UnloggedAccessException;
use App\Security\SSO\BadAuthorizationHeaderFormatException;
use App\Security\SSO\NoAuthHeaderException;
use App\Security\SSO\SSOTokenValidator;
use App\Security\SSO\SSOTokenDecoder;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\HttpFoundation\Request;

/**
 * Instanciates a <code>TelaBotanicaUser</code> from an incoming HTTP request 
 * (or more precisely from its "Authorization" header) or directly from a JWT token .
 *
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
    // Message for the exception thrown when the auth header value is 
    // not valid:
    const BAD_HEADER_FORMAT_MSG         = 'The "Authorization" header value ' .
        'is not valid';
    // Message for the exception thrown when no auth header is available:
    const NO_AUTH_HEADER_MSG            = 'The "Authorization" header ' .
        'is not set';

    private $tokenDecoder;

    public function __construct(SSOTokenDecoder $SSOTokenDecoder)
    {
        $this->tokenDecoder = $SSOTokenDecoder;
    }

    public function extractUser(Request $request) {
        $token = $this->extractTokenFromRequest($request);
        if (null === $token) {
            return null;
        }

        return $this->extractUserFromToken($token);
    }

    private function extractUserFromToken(string $token) {

        if (null === $token) {
            return null;
        }

        $userInfo = $this->tokenDecoder->getUserFromToken($token);
        $roles = array();
        if (in_array( 
            SSOUserExtractor::ADMIN_PERMISSION,
            $userInfo[SSOUserExtractor::PERMISSIONS_TOKEN_PROPERTY] ?? [])) {
            $roles[] = SSOUserExtractor::ADMIN_ROLE;
        }
        else {
            $roles[] = SSOUserExtractor::USER_ROLE;
        }
        $user = new TelaBotanicaUser(
            intval($userInfo['id']), $userInfo['sub'], $userInfo['prenom'], 
            $userInfo['nom'], $userInfo['pseudo'], 
            $userInfo['pseudoUtilise'], $userInfo['avatar'] ?? '',
            $roles, null, $token);

        return $user;
    }



    public function extractTokenFromRequest(Request $request) {
        return $request->headers->get(SSOUserExtractor::TOKEN_HEADER_NAME);
    }

}
