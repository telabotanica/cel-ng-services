<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Exception\InvalidImageException;
use App\Utils\ExifExtractionUtils;
use App\Controller\CreatePhotoAction;

/**
 * An entity representing a Photo.
 *
 * @ORM\Entity
 * @ORM\Table(name="occurrence_validation")
 */
class OccurrenceValidation
{

   /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="IDENTITY")
    * @ORM\Column(type="integer")
    */
   private $id = null;




   /**
     * A Photo can belong to a single Occurrence.
     *
     * @ORM\ManyToOne(targetEntity="Occurrence", inversedBy="photos")
     * @ORM\JoinColumn(name="occurrence_id", referencedColumnName="id")
     */
    private $occurrence;



   public function getId(): ?int
   {
       return $this->id;
   }

   public function getOccurrence(): ?Occurrence
   {
       return $this->occurrence;
   }

   public function setOccurrence(?Occurrence $occurrence): self
   {
       $this->occurrence = $occurrence;

       return $this;
   }

}
