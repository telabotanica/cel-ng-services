<?php


namespace App\Entity;

use App\Exception\InvalidImageException;
use App\Utils\ExifExtractionUtils;
use App\Controller\CreatePhotoAction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * An entity representing a Photo.
 *
 * The API is read-only. Changes are made manually by tela devs using CLI or directly in DB.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}},
 *          "denormalization_context"={"groups"={"write"}},

 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 * @ORM\Entity
 * @ORM\Table(name="taxo_repos")
 */
class TaxoRepo
{

   /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="IDENTITY")
    * @ORM\Column(type="integer")
    * @Groups({"read"})
    */
   private $id = null;

   /**
    * Name.
    *
    * @Assert\NotNull
    * @ORM\Column(name="name", type="string", nullable=false, options={"comment":"Nouveau score de l'observation sur identiplante"})
    * @Groups({"read"})
    */
   private $name = null;

    /**
     * One TaxoRepo has Many Occurrences.
     * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="taxoRepo", cascade={"persist"})
     */
    private $occurrences;


   public function getId(): ?int
   {
       return $this->id;
   }

   public function getName(): ?string
   {
       return $this->name;
   }

   public function setName(?string $name): self
   {
       $this->name = $name;

       return $this;
   }


}
