<?php

namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class TelaBotanicaUser implements UserInterface, EquatableInterface
{
    private $id;
    private $email;
    private $pseudo;
    private $avatar;
    private $surname;
    private $lastName;
    private $usePseudo;
    private $administeredProjectId;
    private $roles;

    public function __construct($id, $email, $surname, $lastName, $pseudo, $usePseudo, $avatar, array $roles, $administeredProjectId)
    {
        $this->id = $id;
        $this->email = $email;
        $this->surname = $surname;
        $this->lastName = $lastName;
        $this->pseudo = $pseudo;
        $this->usePseudo = $usePseudo;
        $this->avatar = $avatar;
        $this->administeredProjectId = $administeredProjectId;
        $this->roles = $roles;
    }



    public function getId()
    {
        return $this->id;
    }

    // @todo put "Admin" in config
    public function isTelaBotanicaAdmin()
    {
        return in_array("administrator", $this->roles);
    }

    public function isProjectAdmin()
    {
        return (!is_null($this->administeredProjectId));
    }

// @todo put "Admin" in config
    public function isLuser()
    {
        return true;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function getAdministeredProjectId()
    {
        return $this->administeredProjectId;
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getUsername()
    {
        return $this->usePseudo ? $this->pseudo : ($this->surname + ' ' + $this->lastName);
    }

    public function getPseudo()
    {
        return $this->pseudo;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof TelaBotanicaUser) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}

