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
class YesAuthenticator  extends AbstractGuardAuthenticator
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
        return true;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        return array(
            'token' => 'sdflsdklfjsdlkfjslkdfjsldk46541qsdf',
        );
    }

    // @todo set administered project ids
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        //$user = new TelaBotanicaUser(22, 'toto@wanadoo.fr', 'toto', 'litoto', 'teehell', 'teehell', '', array('administrator'), null);
        $user = new TelaBotanicaUser(22, 'toto@wanadoo.fr', 'toto', 'litoto', 'teehell', 'teehell', '', array(), null);
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
        return true;
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


