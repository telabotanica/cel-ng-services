<?php

namespace App\Entity;

interface OwnedEntitySimpleInterface
{

    public function getUserId(): ?int;   
    public function setUserId(?int $userId): OwnedEntitySimpleInterface;

}
