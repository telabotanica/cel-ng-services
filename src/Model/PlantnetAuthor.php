<?php

namespace App\Model;

class PlantnetAuthor
{
    /**
     * @var int|string|null
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @return int|string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string|null $id
     * @return PlantnetAuthor
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PlantnetAuthor
     */
    public function setName(string $name): PlantnetAuthor
    {
        $this->name = $name;
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
     * @return PlantnetAuthor
     */
    public function setEmail(string $email): PlantnetAuthor
    {
        $this->email = $email;
        return $this;
    }


}
