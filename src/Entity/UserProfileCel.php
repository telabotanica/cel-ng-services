<?php


namespace App\Entity;

use App\DBAL\LanguageEnumType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CEL user profile. 
 * Gestion des préférences utilisateurs.
 *
 * @ORM\Entity
 * @ORM\Table(name="user_profile_cel", options={"comment":"Gestion des préférences utilisateurs"})
 * @ApiResource(attributes={
 *     "normalization_context"={"groups"={"read"}},
 *     "denormalization_context"={"groups"={"write"}}})
 */
class UserProfileCel
{

   /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="IDENTITY")
    * @ORM\Column(type="integer")
    * @Groups({"read"})    
    */
   private $id = null;


   /**
    * Publisher user ID (null if user is anonymous).
    *
    * Idenfiant utilisateur de lu'tilisateur ayant publié l'observation (null si utilisateur anonyme).
    *
    * @Assert\NotNull
    * @ORM\Column(type="integer", nullable=false)
    * @Groups({"read"})    
    */
   private $userId = null;

   /**
    * Anonymisation des données d'observation.
    * 
    * @Assert\NotNull
    * @ORM\Column(name="anonymize_data", type="boolean", nullable=false, options={"comment":"Anonymisation des données d'observation", "default": false})
    * @Groups({"read", "write"})    
    */
   private $anonymizeData = false;

   /**
    * Validation des conditions d'utilisation.
    *
    * @Assert\NotNull
    * @ORM\Column(name="is_end_user_licence_accepted", type="boolean", nullable=false, options={"comment":"Validation des conditions d'utilisation", "default": false})
    * @Groups({"read", "write"})    
    */
   private $isEndUserLicenceAccepted = false;


   /**
    * L'interface doit-elle afficher les champs avancés ?
    *
    * @Assert\NotNull
    * @ORM\Column(name="always_display_advanced_fields", type="boolean", nullable=false, options={"comment":"Validation des conditions d'utilisation", "default": false})
    * @Groups({"read", "write"})    
    */
   private $alwaysDisplayAdvancedFields = false;


   /**
    * Quel langage doit être utilisé dans l'interface ?
    *
    * @Assert\NotNull
    * @ORM\Column(name="language", type="languageenum", nullable=false, options={"comment":"langage choisi pour communiquer dans l'interface.", "default": LanguageEnumType::FR})
    * @Groups({"read", "write"})    
    */
   private $language = LanguageEnumType::FR;

    /**
     * One UserProfileCel has Many Occurrences.
     * @ORM\OneToMany(targetEntity="Occurrence", mappedBy="userProfile")
     * @Groups({"read", "write"})  
     */
    private $occurrences;



    /**
     * A user can be the admin of a single TelaBotanicaProject.
     *
     * @ORM\ManyToOne(targetEntity="TelaBotanicaProject", inversedBy="administratorProfiles", cascade={"persist"})
     * @ORM\JoinColumn(name="administered_project_id", referencedColumnName="id")
     * @Groups({"read", "write"})  
     */
    private $administeredProject;

    /**
     * The references to CustomUserField this user has created.
     *
     * @ORM\OneToMany(targetEntity="UserCustomField", mappedBy="userProfileCel", cascade={"remove"})
     * @Groups({"read", "write"})  
     */
    private $userCustomFields;


    public function __construct()
    {
        $this->occurrences = new ArrayCollection();
//        $this->administeredProjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

   public function getUserId(): ?int
   {
       return $this->userId;
   }

   public function setUserId(?int $userId): self
   {
       $this->userId = $userId;

       return $this;
   }

    public function getAnonymousData(): ?bool
    {
        return $this->anonymousData;
    }

    public function setAnonymousData(bool $anonymousData): self
    {
        $this->anonymousData = $anonymousData;

        return $this;
    }

    public function getProfileVisibility(): ?bool
    {
        return $this->profileVisibility;
    }

    public function setProfileVisibility(bool $profileVisibility): self
    {
        $this->profileVisibility = $profileVisibility;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getAlwaysDisplayAdvancedFields(): ?bool
    {
        return $this->alwaysDisplayAdvancedFields;
    }

    public function setAlwaysDisplayAdvancedFields(bool $alwaysDisplayAdvancedFields): self
    {
        $this->alwaysDisplayAdvancedFields = $alwaysDisplayAdvancedFields;

        return $this;
    }

    public function getIsEndUserLicenceAccepted(): ?bool
    {
        return $this->isEndUserLicenceAccepted;
    }

    public function setIsEndUserLicenceAccepted(bool $isEndUserLicenceAccepted): self
    {
        $this->isEndUserLicenceAccepted = $isEndUserLicenceAccepted;

        return $this;
    }

    /**
     * @return Collection|Occurrence[]
     */
    public function getOccurrences(): Collection
    {
        return $this->occurrences;
    }

    public function addOccurrence(Occurrence $occurrence): self
    {
        if (!$this->occurrences->contains($occurrence)) {
            $this->occurrences[] = $occurrence;
            $occurrence->setUserProfile($this);
        }

        return $this;
    }

    public function removeOccurrence(Occurrence $occurrence): self
    {
        if ($this->occurrences->contains($occurrence)) {
            $this->occurrences->removeElement($occurrence);
            // set the owning side to null (unless already changed)
            if ($occurrence->getUserProfile() === $this) {
                $occurrence->setUserProfile(null);
            }
        }

        return $this;
    }

    public function getAdministeredProject(): ?TelaBotanicaProject
    {
        return $this->administeredProject;
    }

    public function setAdministeredProject(?TelaBotanicaProject $administeredProject): self
    {
        $this->administeredProject = $administeredProject;

        return $this;
    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;
        }
    }

}
