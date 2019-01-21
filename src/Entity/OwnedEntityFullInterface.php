<?php

namespace App\Entity;

interface OwnedEntityFullInterface extends OwnedEntitySimpleInterface
{

    public function getUserEmail(): ?string;
    public function setUserEmail(?string $userEmail): OwnedEntityFullInterface;
    public function getUserPseudo(): ?string;
    public function setUserPseudo(?string $userPseudo): OwnedEntityFullInterface;

}
