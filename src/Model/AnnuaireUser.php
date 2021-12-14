<?php

namespace App\Model;

class AnnuaireUser
{
    /**
     * @var int|string
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string|null
     */
    private $intitule;

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     * @return AnnuaireUser
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return AnnuaireUser
     */
    public function setEmail(string $email): AnnuaireUser
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    /**
     * @param string|null $intitule
     * @return AnnuaireUser
     */
    public function setIntitule(?string $intitule): AnnuaireUser
    {
        $this->intitule = $intitule;
        return $this;
    }
}
