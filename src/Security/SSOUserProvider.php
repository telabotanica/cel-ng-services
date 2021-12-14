<?php

namespace App\Security;

use App\Security\SSO\SSOUserExtractor;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SSOUserProvider implements UserProviderInterface {

    protected $requestStack;

    private $ssoUserExtractor;

    public function __construct(RequestStack $requestStack, SSOUserExtractor $ssoUserExtractor) {
        $this->requestStack = $requestStack;
        $this->ssoUserExtractor = $ssoUserExtractor;
    }
 
    public function loadUserByUsername($username) {
        //return $this->fetchUser($username);
        return $this->getUser();
    }

    public function refreshUser(UserInterface $user) {
        $request = $this->requestStack->getCurrentRequest();

        return $this->ssoUserExtractor->extractUser($request);
    }

    public function supportsClass($class) {
        return TelaBotanicaUser::class === $class;
    }



}
