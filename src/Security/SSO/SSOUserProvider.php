<?php

namespace App\Security\SSO;

use App\Security\SSO\SSOUserExtractor;
use App\Security\User\TelaBotanicaUser;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\HttpFoundation\Request;

class SSOUserProvider implements UserProviderInterface {

    protected $requestStack;
    protected $userExtractor;

    public function __construct(RequestStack $requestStack, SSOUserExtractor $userExtractor) {
        $this->requestStack = $requestStack;
        $this->userExtractor = $userExtractor;
    }
 
    public function loadUserByUsername($username) {
        return $this->getUser();
    }

    public function refreshUser(UserInterface $user) {
        $request = $this->requestStack->getCurrentRequest();

        return $this->userExtractor->extractUser($request);
    }

    public function supportsClass($class) {
        return TelaBotanicaUser::class === $class;
    }



}
