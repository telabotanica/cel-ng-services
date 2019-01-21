<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Relation entre une observation et un champ étendu. Stocke la valeur de ce 
 * dernier pour l'obs en question.
 *
 * @ORM\Entity
 * @ORM\Table(name="extended_field_occurrence")
 */
class ExtendedFieldOccurrence
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id = null;

    /**
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="Occurrence", inversedBy="extendedFieldOccurrences")
     * @ORM\JoinColumn(name="occurrence_id", referencedColumnName="id", nullable=FALSE)
     */
    private $occurrence;

    /**
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="ExtendedField", inversedBy="extendedFieldValues")
     * @ORM\JoinColumn(name="extended_field_id", referencedColumnName="id", nullable=FALSE)
     */
    private $extendedField;

   /**
    * Valeur renseignée par l'utilisateur.
    *
    * @Assert\NotNull
    * @ORM\Column(type="string", nullable=false, options={"comment":"Valeur renseignée par l'utilisateur"})
    */
   private $value = null;


   public function getId(): ?int
   {
       return $this->id;
   }


   public function getValue(): ?string
   {
       return $this->value;
   }


   public function setValue(string $value): self
   {
       $this->value = $value;

       return $this;
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


   public function getExtendedField(): ?ExtendedField
   {
       return $this->extendedField;
   }


   public function setExtendedField(?ExtendedField $extendedField): self
   {
       $this->extendedField = $extendedField;


       return $this;
   }

}
