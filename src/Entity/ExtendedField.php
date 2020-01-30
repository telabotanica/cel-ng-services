<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * An entity representing an extended field of a 
 * <code>TelaBotanicaProject</code>.
 *
 * @package App\Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="extended_field", uniqueConstraints={@ORM\UniqueConstraint(name="key_fieldid_project", columns={"field_id", "project"})}, options={"comment":"Champs étendus"})
 */
class ExtendedField {


    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id = null;


    /**
     * @Assert\NotNull
     * @ORM\Column(name="field_id", type="string", nullable=false, length=50) 
     */
    private $fieldId = null;

    /**
     * @Assert\NotNull
     * @ORM\Column(name="project", type="string", nullable=false, length=50)
     */
    private $projectName = null;

    /**
     * Type de champ - Texte, Nombre, Date, Booléen.
     *
     * @Assert\NotNull
     * @ORM\Column(name="data_type", type="fielddatatypeenum", nullable=false, length=50, options={"comment":"Type de champ - Texte, Entier, Décimal, Date, Booléen"})
     */
    private $dataType = null;

    /**
     * Champ invisible de l'utilisateur mais nécessaire au projet.
     *
     * @Assert\NotNull
     * @ORM\Column(name="is_visible", type="boolean", nullable=false, options={"comment":"Champ invisible de l'utilisateur mais nécessaire au projet
     "})
     */
    private $isVisible = false;

    /**
     * Indique si le champ est obligatoire pour envoyer la donnée ou non.
     *
     * @Assert\NotNull
     * @ORM\Column(name="is_mandatory", type="boolean", nullable=false, options={"comment":"Indique si le champ est obligatoire pour envoyer la donnée ou non"})
     */
    private $isMandatory = false;

    /**
     * 
     *
     * @ORM\Column(name="min_value", type="decimal", nullable=true, length=10)
     */
    private $minValue = false;

    /**
     * 
     *
     * @ORM\Column(name="max_value", type="decimal", nullable=true, length=10)
     */
    private $maxValue = false;

    /**
     * Format de la valeur (ex adresse mail, numéro de tel).
     *
     * @ORM\Column(type="string", nullable=true, length=255, options={"comment":"Format de la valeur (ex adresse mail, numéro de tel)"})
     */
    private $regexp = true;

    /**
     * @ORM\Column(type="string", nullable=true, length=255, options={"comment":"Unité"})
     */
    private $unit = true;

    /**
     * The TelaBotanicaProject the ExtendedField belongs to.
     *
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="TelaBotanicaProject", inversedBy="extendedFields")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;

    /**
     * The references to occurrences this ExtendedField has values for.
     *
     * @ORM\OneToMany(targetEntity="ExtendedFieldOccurrence", mappedBy="extendedField", cascade={"remove"})
     */
    private $extendedFieldOccurrences;

    /**
     * The translations in various languages for this ExtendedField 
     * description, label, default value and error message.
     *
     * @ORM\OneToMany(targetEntity="ExtendedFieldTranslation", mappedBy="extendedField", cascade={"remove"})
     */
    private $extendedFieldTranslations;

    public function __construct() {
        $this->extendedFieldValues = new ArrayCollection();
    }

    public function getId(): ?int {
 
        return $this->id;
    }

    public function getDataType(): ?string {
        return $this->dataType;
    }
    
    public function setDataType(string $dataType): self {
        $this->dataType = $dataType;

        return $this;
    }

    public function getIsVisible(): ?bool {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): self {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function getIsMandatory(): ?bool {
        return $this->isMandatory;
    }

    public function setIsMandatory(bool $isMandatory): self {
        $this->isMandatory = $isMandatory;

        return $this;
    }

    public function getRegexp(): ?string {
        return $this->regexp;
    }

    public function setRegexp(string $regexp): self {
 
        $this->regexp = $regexp;

        return $this;
    }

    public function getUnit(): ?string {
        return $this->unit;
    }

    public function setUnit(string $unit): self {
        $this->unit = $unit;

        return $this;
    }

    public function getProject(): ?TelaBotanicaProject {
        return $this->project;
    }

    public function setProject(?TelaBotanicaProject $project): self {
        $this->project = $project;

        return $this;
    }

    public function getFieldId(): ?string {
        return $this->fieldId;
    }

    public function setFieldId(?string $fieldId): self {
        $this->project = $fieldId;

        return $this;
    }

    public function getProjectName(): ?string {
        return $this->projectName;
    }

    public function setProjectName(?string $project): self {
        $this->projectName = $projectName;

        return $this;
    }

}
