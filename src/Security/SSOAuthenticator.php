<?php

namespace App\Security;

use App\Security\SSO\SSOTokenValidator;
use App\Security\SSO\SSOTokenDecoder;
use App\Security\User\TelaBotanicaUser;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SSOAuthenticator extends AbstractGuardAuthenticator {

    private $em;

    // Name of the HTTP header containing the auth token:
    const TOKEN_HEADER_NAME             = "Authorization";
    // URL of SSO "annuaire" Web service:
    const SSO_ANNUAIRE_URL              = "http:192.168.0.2";
    // Name of the HTTP header containing the auth token:
    const IGNORE_SSL_ISSUES             = true;
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

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @inheritdoc
     * @internal Should this authenticator be used for the request?
     */
    public function supports(Request $request) {
        return $request->headers->has('Authorization');
    }

    /**
     * @inheritdoc
     * @internal Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request) {
        $headers = $request->headers;
        return array(
            'token' => $headers->get(SSOAuthenticator::TOKEN_HEADER_NAME),
        );
    }

    /**
     * @inheritdoc
     */
    public function getUser(
        $credentials, UserProviderInterface $userProvider) {

        $apiToken = $credentials['token'];

        if (null === $apiToken) {
            return null;
        }

        $tokenDecoder = new SSOTokenDecoder(
            SSOAuthenticator::SSO_ANNUAIRE_URL, 
            SSOAuthenticator::IGNORE_SSL_ISSUES);
        $userInfo = $tokenDecoder->getUserFromToken($token);
        $role = new Role();

        if (in_array(
            $userInfo[SSOAuthenticator::PERMISSIONS_TOKEN_PROPERTY], 
            SSOAuthenticator::ADMIN_PERMISSION)) {

            $role->setName(SSOAuthenticator::ADMIN_ROLE_NAME);
            $role->setRole(SSOAuthenticator::ADMIN_ROLE);
        }
        else {
            $role->setName(SSOAuthenticator::USER_ROLE_NAME);
            $role->setRole(SSOAuthenticator::USER_ROLE);
        }

        $user = new TelaBotanicaUser(
            $userInfo['id'], $userInfo['sub'], $userInfo['prenom'], 
            $userInfo['nom'], $userInfo['pseudo'], 
            $userInfo['pseudoUtilise'], $userInfo['avatar'], 
            array($role), []);

        // Returns the user, checkCredentials() is gonna be called
        return $user;
    }

    /**
     * Checks if the SSO JWT token is valid.
     * Returns true if that's the case (which will cause authentication 
     * success), else false.
     */
    public function checkCredentials($credentials, UserInterface $user) {
        if (null === $token) {
            return false;
        }

        $token = $credentials['token'];
        $tokenValidator = new SSOTokenValidator(
            SSOAuthenticator::SSO_ANNUAIRE_URL, 
            SSOAuthenticator::IGNORE_SSL_ISSUES);

        return $tokenValidator->validateToken($token);
    }

    public function onAuthenticationSuccess(
        Request $request, TokenInterface $token, $providerKey) {

        // Just let the request roll
        return null;
    }

    public function onAuthenticationFailure(
        Request $request, AuthenticationException $ex) {

        $data = array(
            'message' => strtr($ex->getMessageKey(), $ex->getMessageData())
            // WHEN TRANSLATING, USE THIS:
            // $this->translator->trans($ex->getMessageKey(), $ex->getMessageData())
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed - it's not sent.
     */
    public function start(
        Request $request, AuthenticationException $ex = null) {

        $data = array(
            // you might translate this message
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe() {
        return false;
    }

}


