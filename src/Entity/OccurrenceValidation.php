<?php

namespace App\Entity;

use App\Exception\InvalidImageException;
use App\Utils\ExifExtractionUtils;
use App\Controller\CreatePhotoAction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a third-party taxonomic validation of an <code>Occurrence</code>.
 *
 * @package App\Entity  
 *
 * @ORM\Entity
 * @ORM\Table(name="occurrence_validation")
 */
class OccurrenceValidation {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id = null;
        
    /**
      * A Photo can belong to a single Occurrence.
      *
      * @ORM\ManyToOne(targetEntity="Occurrence", inversedBy="validations")
      * @ORM\JoinColumn(name="occurrence_id", referencedColumnName="id")
      */
    private $occurrence;
       
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getOccurrence(): ?Occurrence {
        return $this->occurrence;
    }

    public function setOccurrence(?Occurrence $occurrence): self {
        $this->occurrence = $occurrence;

        return $this;
    }

}
