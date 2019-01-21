<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Security\SSO\SSOTokenValidator;
use App\Security\SSO\SSOTokenDecoder;
use App\Security\User\TelaBotanicaUser;

// @todo handle translations?
// @todo header name in conf?
class SSOAuthenticator  extends AbstractGuardAuthenticator
{


    private $em;
    // @todo put these in config
    private $tokenHeaderName = "Authorization";
    private $annuaireURL = "Authorization";
    private $ignoreSSLIssues = "Authorization";

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        return array(
            'token' => $request->headers->get($this->tokenHeaderName),
        );
    }

    // @todo set administered project ids
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiToken = $credentials['token'];

        if (null === $apiToken) {
            return null;
        }

        $tokenDecoder = new SSOTokenDecoder($this->annuaireURL, $this->ignoreSSLIssues);
        $userInfo = $tokenDecoder->getUserFromToken($token);

        $role = new Role();


        if (in_array($userInfo['permissions'], 'administrator')) {
            $role->setName('Admin');
            $role->setRole('ROLE_ADMIN');
        }
        else {
            $role->setName('User');
            $role->setRole('ROLE_USER');
        }
        $user = new TelaBotanicaUser($userInfo['id'], $userInfo['sub'], $userInfo['prenom'], $userInfo['nom'], $userInfo['pseudo'], $userInfo['pseudoUtilise'], $userInfo['avatar'], array($role), []);

        // if a User object, checkCredentials() is called
        return $user;
    }

    /**
     * Checks credentials - e.g. make sure the SSO JWT token is valid.
     * Returns true if that's the case (which will cause authentication 
     * success), else false.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if (null === $token) {
            return false;
        }

        $token = $credentials['token'];
        $tokenValidator = new SSOTokenValidator($this->annuaireURL, $this->ignoreSSLIssues);

        return $tokenValidator->validateToken($token);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // Just let the request roll!
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // you might translate this message
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}


