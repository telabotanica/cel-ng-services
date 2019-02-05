<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TelaBotanicaUserProvider
{
    private $tokenStorage;
 
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
 
    /**
     * Get the logged in user or null.
     *
     * @return User
     */
    public function getUser()
    {
        $user = null;
        $token = $this->tokenStorage->getToken();

        if ($token !== null) {
            $user = $token->getUser();
        }
 
        return $user;
    }
}
