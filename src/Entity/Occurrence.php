<?php

namespace App\Entity;

use App\DBAL\TaxoRepoEnumType;
use App\DBAL\CertaintyEnumType;
use App\DBAL\PublishedLocationEnumType;
use App\DBAL\OccurrenceTypeEnumType;
use App\DBAL\InputSourceEnumType;
use App\Controller\OccurrenceBulkAction;
use App\Controller\ImportOccurrenceAction;
use App\Entity\Photo;
use App\Entity\TaxoRepo;
use App\Filter\Occurrence\IsPublicFilter;
use App\Filter\Occurrence\CertaintyFilter;
use App\Filter\Occurrence\DateObservedYearFilter;
use App\Filter\Occurrence\DateObservedMonthFilter;
use App\Filter\Occurrence\CountryFilter;
use App\Filter\Occurrence\CountyFilter;
use App\Filter\Occurrence\DateObservedDayFilter;
use App\Filter\Occurrence\LocalityFilter;
use App\Filter\Occurrence\UserSciNameFilter;
use App\Filter\Occurrence\FamilyFilter;
use App\Filter\Occurrence\IdentiplanteScoreFilter;
use App\Filter\Occurrence\IsIdentiplanteValidatedFilter;
use App\Filter\Occurrence\ProjectIdFilter;
use App\Entity\OwnedEntityFullInterface;
use App\Entity\OwnedEntitySimpleInterface;
use App\Entity\TimestampedEntityInterface;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Entity representing a botanic occurrence (observation).
 *
 * @ApiResource(attributes={
 *      "normalization_context"={"groups"={"read"}},
 *      "formats"={"jsonld", "json", "geojson"={"application/vnd.geo+json"}, "jsonpatch"={"application/json-patch+json"}, "pdf"={"application/pdf"}},
 *      "denormalization_context"={"groups"={"write"}},
 *      "filters"={IsPublicFilter::class, CertaintyFilter::class, DateObservedYearFilter::class, DateObservedMonthFilter::class, DateObservedDayFilter::class, UserSciNameFilter::class, CountryFilter::class, IdentiplanteScoreFilter::Class, IsIdentiplanteValidatedFilter::Class, CountyFilter::Class, FamilyFilter::Class, ProjectIdFilter::Class}},
 *      itemOperations={
 *          "get"={"method"="GET", "access_control"="is_granted('view', object)"},
 *          "patch"={"method"="PATCH", "access_control"="is_granted('edit', object)"},
 *          "put"={"method"="PUT", "access_control"="is_granted('edit', object)"},
 *          "delete"={"method"="DELETE", "access_control"="is_granted('delete', object)"}
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={"method"="POST"},
 *          "bulk"={
 *              "method"="PATCH",
 *              "controller"=OccurrenceBulkAction::class,
 *              "swagger_context"={
 *                  "parameters"={},
 *                  "responses"={ 
 *                      "207"= {
 *                          "description" = "The bulk operation was performed succesfully."
 *                      },
 *                      "500"= {
 *                          "description" = "An error occured during bulk operation."
 *                      }
 *                  },
 *                  "summary" = "Bulk treatment for DELETE and UPDATE operations for Occurrence resources.",
 *                  "consumes" = "json-patch+json",
 *                  "produces" = "application/json"
 *              }
 *          },
 *          "import"={
 *              "method"="POST",
 *              "path"="/occurrences/import",
 *              "controller"=ImportOccurrenceAction::class,
 *              "swagger_context"={
 *                  "parameters"={},
 *                  "responses"={ 
 *                      "207"= {
 *                          "description" = "The import was performed succesfully."
 *                      },
 *                      "500"= {
 *                          "description" = "An error occured during import."
 *                      }
 *                  },
 *                  "summary" = "Import Occurrence resources by uploading a spreadsheet file (excel or CSV).",
 *                  "produces" = "application/json"
 *              }
 *          }
 *      }
 * )
 *
 * //AT UniqueEntity(fields={"signature"}, message="It seems a duplicate occurrence already exists in CEL. Il semblerait que cette observation soit déjà présente dans votre carnet en ligne")
 * @ORM\Entity(repositoryClass="App\Repository\OccurrenceRepository")
 * @ORM\Table(name="occurrence", indexes={@ORM\Index(name="user_id_idx", columns={"user_id"})})
 * @todo use JSON type for geometry if possible to have MariaDB 10.2.7.+ in prod
 * @todo elevation string -> float
 */
class Occurrence implements OwnedEntityFullInterface, TimestampedEntityInterface
{

   /**
    * @Groups({"read"})
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="IDENTITY")
    */
   private $id = null;

   /**
    * Publisher user ID (null if he was anonymous).
    *
    * Idenfiant de l'utilisateur ayant publié l'observation (null si utilisateur anonyme).
    *
    * @Groups({"read"})
    * @ORM\Column(name="user_id", type="integer", nullable=true, options={"comment":"id de l'utilisateur ayant saisi l'obs (seulement identification de tela, si utilisateur non inscrit ce champ est vide)"})
    */
   private $userId = null;


   /**
    * Email de l'utilisateur ayant saisi l'obs.
    *
    * @Assert\Email
    * @Groups({"read"})
    * @ORM\Column(name="user_email", type="string", nullable=false, options={"comment":"Email de l'utilisateur ayant saisi l'obs"})
    */
   private $userEmail = null;

   /**
    * Pseudo de l'utilisateur ayant saisi l'obs. Nom/Prénom si non renseigné.
    *
    * @Groups({"read"})
    * @ORM\Column(name="user_pseudo", type="string", nullable=true, options={"comment":"Pseudo de l'utilisateur ayant saisi l'obs. Nom/Prénom si non renseigné."})
    */
   private $userPseudo = null;


   /**
    * Observateur.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(type="string", nullable=false, options={"comment":"Observateur"})
    */
   private $observer = null;

   /**
    * Structure dans le cadre de laquelle l'obs a été faite.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="observer_institution", type="string", nullable=true, options={"comment":"Structure dans le cadre de laquelle l'obs a été faite"})
    */
   private $observerInstitution = null;

   /**
    * Date de l'obs.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="date_observed", type="datetime", nullable=true, options={"comment":"Date d'observation"})
    */
   private $dateObserved = null;

   /**
    * Date de création de l'obs.
    *
    * @Groups({"read"})
    * @ORM\Column(name="date_created", type="datetime", nullable=false, options={"comment":"Date de création de l'obs"})
    */
   private $dateCreated = null;

   /**
    * Date de la dernière modification de l'obs.
    *
    * @Groups({"read"})
    * @ORM\Column(name="date_updated", type="datetime", nullable=true, options={"comment":"Date de la dernière modification de l'obs"})
    */
   private $dateUpdated = null;


   /**
    * Date de publication de l'obs = transmission au réseau.
    *
    * @Groups({"read"})
    * @ORM\Column(name="date_published", type="datetime", nullable=true, options={"comment":"Date de publication de l'obs = transmission au réseau"})
    */
   private $datePublished = null;



   /**
    * Nom saisi par l'utilisateur (nom scientifique ou autre terme qualifiant
    * l'individu observé).
    * 
    * @Groups({"read", "write"})
    * @ORM\Column(name="user_sci_name", type="string", nullable=true, options={"comment":"Nom saisi par l'utilisateur (nom scientifique ou autre terme qualifiant  l'individu observé)"})
    */
   private $userSciName = null;

   /**
    * Numéro du nom saisi par l'utilisateur, dans le cas où celui-ci est lié 
    * à un référentiel.
    * 
    * @Groups({"read", "write"})
    * @ORM\Column(name="user_sci_name_id", type="integer", nullable=true, options={"comment":"Numéro du nom (ou numéro nomenclatural ou nn) saisi par l'utilisateur, dans le cas où celui-ci est lié à un référentiel"})
    */
   private $userSciNameId = null;

   /**
    * Nom retenu
    *
    * @Groups({"read"})
    * @ORM\Column(name="accepted_sci_name", type="string", nullable=true, options={"comment":"Nom retenu"})
    */
   private $acceptedSciName = null;

   /**
    * Numéro du nom retenu.
    *
    * @Groups({"read"})
    * @ORM\Column(name="accepted_sci_name_id", type="integer", nullable=true, options={"comment":"Numéro du nom (ou numéro nomenclatural ou nn) retenu"})
    */
   private $acceptedSciNameId = null;

   /**
    * Identifiant plantnet.
    *
    * @Groups({"read"})
    * @ORM\Column(name="plantnet_id", type="integer", nullable=true, options={"comment":"Identifiant plantnet"})
    */
   private $plantnetId = null;



   /**
    * Famille du taxon auquel appartient l'observation.
    *
    * @Groups({"read"})
    * @ORM\Column(name="family", type="string", nullable=true, options={"comment":"Famille du taxon auquel appartient l'observation"})
    */
   private $family = null;




    /**
     * Référentiel taxonomique
     * 
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity=TaxoRepo::class)
     */
    private $taxoRepo;

   /**
    * Certitude de l'identification taxonomique.
    * 
    * @Groups({"read", "write"})
    * @ORM\Column(type="certaintyenum", nullable=true, options={"comment":"Certitude de l identification taxonomique"})
    */
   private $certainty;

   /**
    * Commentaires concernant l'obs.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(type="string", nullable=true, options={"comment":"Commentaires concernant l'obs"})
    */
   private $annotation = null;

   /** 
    * Type de donnée - observation de terrain, issue de la bibliographie, 
    * donnée d'herbier.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="occurrence_type", type="occurrencetypeenum", nullable=true, options={"comment":"Type de donnée - observation de terrain, issue de la bibliographie, donnée d'herbier", "default": OccurrenceTypeEnumType::FIELD})
    */
   private $occurrenceType = OccurrenceTypeEnumType::FIELD;

   /**
    * Indique si l'individu observé était sauvage ou cultivé.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="is_wild", type="boolean", nullable=true, options={"comment":"Indique si l'individu observé était sauvage ou cultivé", "default": true})
    */
   private $isWild = true;

   /**
    * @Groups({"read", "write"})
    * @ORM\Column(type="integer", nullable=true)
    */
   private $coef = null;

   /**
    * Stade phénologique observé - échelle BBCH, stades regroupés par 10 
    * (sauf certains stades remarquables).
    *
    * @Groups({"read", "write"})
    * @ORM\Column(type="phenologyenum", nullable=true, options={"comment":"Stade phénologique observé - échelle BBCH, stades regroupés par 10 (sauf certains stades remarquables)"})
    */
   private $phenology = null;

   /**
    * Indique la présence / l'absence d'une part d'herbier associée à l'obs.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="sample_herbarium", type="boolean", nullable=true, options={"comment":"Indique la présence / l'absence d'une part d'herbier associée à l'obs", "default": false})
    */
   private $sampleHerbarium = false;


   /**
    * Source bibliographique.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="bibliography_source", type="string", nullable=true, options={"comment":"Source bibliographique"})
    */
   private $bibliographySource;

   /**
    * Interface utilisée pour la saisie de l'obs - CEL, VegLab, widget, 
    * PlantNet, autre.
    *
    * @Assert\NotNull
    * @Groups({"read"})
    * @ORM\Column(name="input_source", type="inputsourceenum", nullable=true, options={"comment":"Interface utilisée pour la saisie de l'obs - CEL, VegLab, widget,  PlantNet, autre"})
    */
   private $inputSource = InputSourceEnumType::CEL;

   /**
    * Indique si l'obs est publique ou non.
    *
    * @Assert\NotNull
    * @Groups({"read", "write"})
    * @ORM\Column(name="is_public", type="boolean", nullable=false, options={"comment":"Indique si l'obs est publique ou non", "default": false})
    */
   private $isPublic = false;

   /**
    * Indique si l'obs s'affiche dans le CEL ou non.
    *
    * @Assert\NotNull
    * @ORM\Column(name="is_visible_in_cel", type="boolean", nullable=false, options={"comment":"Indique si l'obs s'affiche dans le CEL ou non", "default": true})
    */
   private $isVisibleInCel = true;

   /**
    * Indique si l'obs s'affiche dans VegLab ou non. 
    *
    * @Assert\NotNull
    * @ORM\Column(name="is_visible_in_veg_lab", type="boolean", nullable=false, options={"comment":"Indique si l'obs s'affiche dans VegLab ou non", "default": false})
    */
   private $isVisibleInVegLab = false;

   /**
    * Vérification des doublons.
    * Signature of an occurrence: should be unique. Used to detect duplicates
    * based on what the of fields which makes an occurrence unique.
    *
    * @ORM\Column(type="string", nullable=false, options={"comment":"Vérification des doublons"})
    */
   private $signature = null;


   /**
    * Localisation précise de l'obs.
    * GeoJSON geometry fo this occurrence.
    *
    * @Groups({"read", "write"})    
    * @ORM\Column(type="text", nullable=true, options={"comment":"Localisation précise de l'obs"})
    */
   private $geometry = null;


   /**
    * Altitude.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(type="integer", nullable=true, options={"comment":"Altitude"})
    */
   private $elevation = null;

   /**
    * Système géodésique.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(type="string", nullable=true, options={"comment":"Système géodésique", "default": "WGS84"})
    */
   private $geodatum = 'WGS84';

   /**
    * Localité où se trouve l'obs.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(type="string", nullable=true, options={"comment":"Localité où se trouve l'obs"})
    */
   private $locality = null;

   /**
    * Lieu-dit.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(type="string", nullable=true, options={"comment":"Lieu-dit"})
    */
   private $sublocality = null;

   /**
    * Milieu, type d'habitat. 
    *
    * @Groups({"read", "write"})
    * @ORM\Column(type="string", nullable=true, options={"comment":"Milieu, type d'habitat"})
    */
   private $environment = null;


   /**
    * Cohérence entre les coordonnées et la localité.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="locality_consistency", type="boolean", nullable=true, options={"comment":"Cohérence entre les coordonnées et la localité"})
    */
   private $localityConsistency = null;


   /**
    * @Groups({"read", "write"})
    * @ORM\Column(type="string", nullable=true, options={"comment":"The string to show in the dropdown "})
    */
   private $station = null;

   /**
    * Précision géographique à laquelle est publiée l'obs, permet de gérer le 
    * floutage - Précise, Localité, Maille 10x10km.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="published_location", type="publishedlocationenum", nullable=true, options={"comment":"Précision géographique à laquelle est publiée l'obs, permet de gérer le floutage", "default": PublishedLocationEnumType::PRECISE})
    */
   private $publishedLocation = PublishedLocationEnumType::PRECISE;

   /**
    * Précision (ou incertitude) de la localisation.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="location_accuracy", type="locationaccuracytypeenum", nullable=true, options={"comment":"Précision (ou incertitude) de la localisation"})
    */
   private $locationAccuracy = null;

   /**
    * Champ complété automatiquement par osm - comté.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="osm_county", type="string", nullable=true, options={"comment":"Champ complété automatiquement par osm - comté"})
    */
   private $osmCounty = null;

   /**
    * Champ complété automatiquement par osm - état.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="osm_state", type="string", nullable=true, options={"comment":"Champ complété automatiquement par osm - état"})
    */
   private $osmState = null;

   /**
    * Champ complété automatiquement par osm - code postal.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="osm_postcode", type="string", nullable=true, options={"comment":"Champ complété automatiquement par osm - code postal"})
    */
   private $osmPostcode = null;

   /**
    * Champ complété automatiquement par osm - pays.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="osm_country", type="string", nullable=true, options={"comment":"Champ complété automatiquement par osm - pays"})
    */
   private $osmCountry = null;

   /**
    * Champ complété automatiquement par osm - code pays.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="osm_country_code", type="string", nullable=true, options={"comment":"Champ complété automatiquement par osm - code pays"})
    */
   private $osmCountryCode = null;

   /**
    * Champ complété automatiquement par osm - id osm.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="osm_id", type="integer", nullable=true, options={"comment":"Champ complété automatiquement par osm - id osm"})
    */
   private $osmId = null;

   /**
    * Champ complété automatiquement par osm - id de l'instance géographique.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="osm_place_id", type="integer", nullable=true, options={"comment":"Champ complété automatiquement par osm - id de l'instance géographique"})
    */
   private $osmPlaceId = null;

   /**
    * IdentiPlante (DEL) score for the Occurrence.
    *
    * @Groups({"read"})
    * @ORM\Column(name="identiplante_score", type="integer", nullable=true, options={"default": 0, "comment":"Score de l'observation sur identiplante"})
    */
   private $identiplanteScore = null;

   /**
    * IdentiPlante (DEL) validation status for the Occurrence.
    *
    * @Assert\NotNull
    * @Groups({"read"})
    * @ORM\Column(name="is_identiplante_validated", type="boolean", nullable=false, options={"default": false, "comment":"Statut validé (ou non) de l'observation sur identiplante"})
    */
   private $isIdentiplanteValidated = false;


   /**
    * Champ complété automatiquement par osm - code pays.
    *
    * @Groups({"read", "write"})
    * @ORM\Column(name="identification_author", type="string", nullable=true, options={"comment":"Nom de la personne ayant identifié l'espèce observée (si différente de l'observateur)"})
    */
   private $identificationAuthor = null;

   /**
     * The tela botanica project the occurrence belongs to.
     *
     * @Groups({"read", "write"})
     * @ORM\ManyToOne(targetEntity="TelaBotanicaProject", inversedBy="occurrences")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;

   /**
     * Many Occurrences have One UserProfileCel.
     * @ORM\ManyToOne(targetEntity="UserProfileCel", inversedBy="occurrences")
     * @ORM\JoinColumn(name="user_profile_id", referencedColumnName="id")
     */
    private $userProfile;

    /**
     * One Occurrence can have many attached photos.
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="occurrence", cascade={"persist"})
     * @ApiSubresource(maxDepth=1)
     */
    private $photos;

    /**
     * One Occurrence can have many associated validations.
     * @ORM\OneToMany(targetEntity="OccurrenceValidation", mappedBy="occurrence")
     * @ApiSubresource(maxDepth=1)
     */
    private $validations;

    /**
     * Notifications that the status of the Occurence was changed on IdentiPlante (DEL). 
     * One Occurrence can have many associated notifications.
     * ATimpl to keep the eslasticsearch Occurrence index in sync with IdentiPlante (DEL).
     * @ORM\OneToMany(targetEntity="DelUpdateNotification", mappedBy="occurrence")
     * @ApiSubresource(maxDepth=1)
     */
    private $delUpdateNotifications;

    /**
     * The values for ExtendedField attached to this occurrence.
     *
     * @ORM\OneToMany(targetEntity="ExtendedFieldOccurrence", mappedBy="occurrence", cascade={"remove"})
     */
    private $extendedFieldOccurrences;

    /**
     * The values for UserCustomFields attached to this occurrence.
     *
     * @ORM\OneToMany(targetEntity="UserCustomFieldOccurrence", mappedBy="occurrence", cascade={"remove"})
     */
    private $userCustomFieldOccurrences;


    /**
     * @ORM\OneToMany(targetEntity=OccurrenceUserOccurrenceTagRelation::class, cascade={"persist", "remove"}, mappedBy="occurrence")
     * @ApiSubresource(maxDepth=1)
     */
    protected $userTagRelations;

   public function setId(?int $id): self
   {
       $this->id = $id;

       return $this;
   }

   public function getId(): ?int
   {
       return $this->id;
   }

   public function getUserId(): ?int
   {
       return $this->userId;
   }

   public function setUserId(?int $userId): OwnedEntitySimpleInterface
   {
       $this->userId = $userId;

       return $this;
   }

   public function getUserEmail(): ?string
   {
       return $this->userEmail;
   }

   public function setUserEmail(?string $userEmail): OwnedEntityFullInterface
   {
       $this->userEmail = $userEmail;

       return $this;
   }

   public function getUserPseudo(): ?string
   {
       return $this->userPseudo;
   }

   public function setUserPseudo(?string $userPseudo): OwnedEntityFullInterface
   {
       $this->userPseudo = $userPseudo;

       return $this;
   }

 
   public function getObserver(): ?string
   {
       return $this->observer;
   }

   public function setObserver(string $observer): self
   {
       $this->observer = $observer;

       return $this;
   }

   public function getObserverInstitution(): ?string
   {
       return $this->observerInstitution;
   }

   public function setObserverInstitution(string $observerInstitution): self
   {
       $this->observerInstitution = $observerInstitution;

       return $this;
   }

   public function getDateObserved(): ?\DateTimeInterface
   {
       return $this->dateObserved;
   }

   public function getFormattedDateObserved(): ?string
   {
       return (null !== $this->dateObserved) ? $this->dateObserved->format('Y-m-d H:i:s') : null;
   }


   public function getFormattedDateCreated(): ?string
   {
       return (null !== $this->dateCreated) ? $this->dateCreated->format('Y-m-d H:i:s') : null;
   }

   public function getFormattedDateUpdated(): ?string
   {
       return (null !== $this->dateUpdated) ? $this->dateUpdated->format('Y-m-d H:i:s') : null;
   }

   public function getFormattedDatePublished(): ?string
   {
       return (null !== $this->datePublished) ? $this->datePublished->format('Y-m-d H:i:s') : null;
   }

   public function getDateObservedMonth(): ?float
   {
	if ( null !== $this->dateObserved ) {
		return $this->dateObserved->format('m');
	}
	return null;
   }

   public function getDateObservedDay(): ?float
   {
	if ( null !== $this->dateObserved ) {
		return $this->dateObserved->format('d');
	}
	return null;
   }

   public function getDateObservedYear(): ?float
   {
	if ( null !== $this->dateObserved ) {
		return $this->dateObserved->format('Y');
	}
	return null;
   }


   public function setDateObserved(\DateTimeInterface $dateObserved): self
   {
       $this->dateObserved = $dateObserved;

       return $this;
   }

   public function setDateCreated(?\DateTimeInterface $dateCreated): TimestampedEntityInterface
   {
       $this->dateCreated = $dateCreated;

       return $this;
   }

   public function getDateCreated(): ?\DateTimeInterface
   {
       return $this->dateCreated;
   }

   public function setDateUpdated(?\DateTimeInterface $dateUpdated): TimestampedEntityInterface
   {
       $this->dateUpdated = $dateUpdated;

       return $this;
   }

   public function getDateUpdated(): ?\DateTimeInterface
   {
       return $this->dateUpdated;
   }

   public function getDatePublished(): ?\DateTimeInterface
   {
       return $this->datePublished;
   }

   public function setDatePublished(\DateTimeInterface $datePublished): self
   {
       $this->datePublished = $datePublished;

       return $this;
   }

   public function getUserSciName(): ?string
   {
       return $this->userSciName;
   }

   public function setUserSciName(string $userSciName): self
   {
       $this->userSciName = $userSciName;

       return $this;
   }

   public function getUserSciNameId(): ?int
   {
       return $this->userSciNameId;
   }

   public function setUserSciNameId(?int $userSciNameId): self
   {
       $this->userSciNameId = $userSciNameId;

       return $this;
   }



   public function getAcceptedSciName(): ?string
   {
       return $this->acceptedSciName;
   }


   public function getAcceptedSciNameId(): ?int
   {
       return $this->acceptedSciNameId;
   }

   public function getValidSciName(): ?string
   {
       return $this->validSciName;
   }

   public function getCoef(): ?int
   {
       return $this->coef;
   }

   public function setCoef(?int $coef): self
   {
       $this->coef = $coef;

       return $this;
   }

   public function setValidSciName(string $validSciName): self
   {
       $this->validSciName = $validSciName;

       return $this;
   }

   public function getValidSciNameId(): ?int
   {
       return $this->validSciNameId;
   }

   public function setValidSciNameId(int $validSciNameId): self
   {
       $this->validSciNameId = $validSciNameId;

       return $this;
   }

   public function getPlantnetId(): ?int
   {
       return $this->plantnetId;
   }

   public function setFamily(string $family): self
   {
       $this->family = $family;

       return $this;
   }

   public function getFamily(): ?string
   {
       return $this->family;
   }



   public function getTaxoRepo() : ?TaxoRepo
   {
       return $this->taxoRepo;
   }

   public function setTaxoRepo(?TaxoRepo $taxoRepo): self
   {
       $this->taxoRepo = $taxoRepo;

       return $this;
   }

   public function getPhenology(): ?string
   {
       return $this->phenology;
   }

   public function setPhenology($phenology): self
   {
       $this->phenology = $phenology;

       return $this;
   }

   public function getCertainty(): ?string
   {
       return $this->certainty;
   }

   public function setCertainty(string $certainty): self
   {
       $this->certainty = $certainty;

       return $this;
   }

   public function getAnnotation(): ?string
   {
       return $this->annotation;
   }

   public function setAnnotation(string $annotation): self
   {
       $this->annotation = $annotation;

       return $this;
   }

   public function getOccurrenceType()
   {
       return $this->occurrenceType;
   }

   public function setOccurrenceType($occurrenceType): self
   {
       $this->occurrenceType = $occurrenceType;

       return $this;
   }

   public function getIsWild(): ?bool
   {
       return $this->isWild;
   }

   public function setIsWild(bool $isWild): self
   {
       $this->isWild = $isWild;

       return $this;
   }

   public function getIndividualCount(): ?int
   {
       return $this->individualCount;
   }

   public function setIndividualCount(int $individualCount): self
   {
       $this->individualCount = $individualCount;

       return $this;
   }

   public function getSampleHerbarium(): ?bool
   {
       return $this->sampleHerbarium;
   }

   public function setSampleHerbarium(bool $sampleHerbarium): self
   {
       $this->sampleHerbarium = $sampleHerbarium;

       return $this;
   }

   public function getBibliographySource()
   {
       return $this->bibliographySource;
   }

   public function getInputSource()
   {
       return $this->inputSource;
   }

   public function setInputSource($inputSource): self
   {
       $this->inputSource = $inputSource;

       return $this;
   }

   public function getIsPublic(): ?bool
   {
       return $this->isPublic;
   }

   public function setIsPublic(bool $isPublic): self
   {
       $this->isPublic = $isPublic;

       return $this;
   }

   public function getIsVisibleInCel(): ?bool
   {
       return $this->isVisibleInCel;
   }

   public function setIsVisibleInCel(bool $isVisibleInCel): self
   {
       $this->isVisibleInCel = $isVisibleInCel;

       return $this;
   }

   public function getIsVisibleInVegLab(): ?bool
   {
       return $this->isVisibleInVegLab;
   }

   public function setIsVisibleInVegLab(bool $isVisibleInVegLab): self
   {
       $this->isVisibleInVegLab = $isVisibleInVegLab;

       return $this;
   }

   public function getSignature(): ?string
   {
       return $this->signature;
   }


   public function getGeodatum(): ?string
   {
       return $this->geodatum;
   }

   public function setGeodatum(?string $geodatum): self
   {
       $this->geodatum = $geodatum;

       return $this;
   }

   public function getLocalityConsistency(): ?bool
   {
       return $this->localityConsistency;
   }


   public function getLocality(): ?string
   {
       return $this->locality;
   }

   public function setLocality(?string $locality): self
   {
       $this->locality = $locality;

       return $this;
   }

   public function getSublocality(): ?string
   {
       return $this->sublocality;
   }

   public function setSublocality(?string $sublocality): self
   {
       $this->sublocality = $sublocality;

       return $this;
   }

   public function getEnvironment(): ?string
   {
       return $this->environment;
   }

   public function setEnvironment(?string $environment): self
   {
       $this->environment = $environment;

       return $this;
   }

   public function getStation(): ?string
   {
       return $this->station;
   }

   public function setStation(?string $station): self
   {
       $this->station = $station;

       return $this;
   }

   public function getPublishedLocation()
   {
       return $this->publishedLocation;
   }

   public function setPublishedLocation($publishedLocation): self
   {
       $this->publishedLocation = $publishedLocation;

       return $this;
   }

   public function getLocationAccuracy()
   {
       return $this->locationAccuracy;
   }

   public function setLocationAccuracy($locationAccuracy): self
   {
       $this->locationAccuracy = $locationAccuracy;

       return $this;
   }


   public function setGeometry(?string $geometry): self
   {
       $this->geometry = $geometry;
        return $this;
   }

   public function getGeometry(): ?string
   {
       return $this->geometry;
   }


   public function getElevation(): ?string
   {
       return $this->elevation;
   }

   public function setElevation(?string $elevation): self
   {
       $this->elevation = $elevation;

       return $this;
   }

   public function getOsmCounty(): ?string
   {
       return $this->osmCounty;
   }

   public function setOsmCounty(?string $osmCounty): self
   {
       $this->osmCounty = $osmCounty;

       return $this;
   }

   public function getOsmState(): ?string
   {
       return $this->osmState;
   }

   public function setOsmState(?string $osmState): self
   {
       $this->osmState = $osmState;

       return $this;
   }

   public function getOsmPostcode(): ?string
   {
       return $this->osmPostcode;
   }

   public function setOsmPostcode(?string $osmPostcode): self
   {
       $this->osmPostcode = $osmPostcode;

       return $this;
   }

   public function getOsmCountry(): ?string
   {
       return $this->osmCountry;
   }

   public function setOsmCountry(?string $osmCountry): self
   {
       $this->osmCountry = $osmCountry;

       return $this;
   }

   public function getOsmCountryCode(): ?string
   {
       return $this->osmCountryCode;
   }

   public function setOsmCountryCode(?string $osmCountryCode): self
   {
       $this->osmCountryCode = $osmCountryCode;

       return $this;
   }

   public function getOsmId(): ?int
   {
       return $this->osmId;
   }

   public function setOsmId(int $osmId): self
   {
       $this->osmId = $osmId;

       return $this;
   }

   public function getOsmPlaceId(): ?int
   {
       return $this->osmPlaceId;
   }

   public function setOsmPlaceId(int $osmPlaceId): self
   {
       $this->osmPlaceId = $osmPlaceId;

       return $this;
   }


   public function getIdentiplanteScore(): ?int
   {
       return $this->identiplanteScore;
   }

   public function setIdentiplanteScore(?int $identiplanteScore): self
   {
       $this->identiplanteScore = $identiplanteScore;

       return $this;
   }

   public function getIsIdentiplanteValidated(): ?bool
   {
       return $this->isIdentiplanteValidated;
   }

   public function setIsIdentiplanteValidated(bool $isIdentiplanteValidated): self
   {
       $this->isIdentiplanteValidated = $isIdentiplanteValidated;

       return $this;
   }
   


   public function getIdentificationAuthor(): ?string
   {
       return $this->identificationAuthor;
   }

   public function setIdentificationAuthor(?string $identificationAuthor): self
   {
       $this->identificationAuthor = $identificationAuthor;

       return $this;
   }


   public function __construct()
   {
        $this->photos = new ArrayCollection();
        $this->userTagRelations = new ArrayCollection();
        $this->extendedFieldValues = new ArrayCollection();
   }

   public function getProject(): ?TelaBotanicaProject
   {
       return $this->project;
   }

   public function setProject(?TelaBotanicaProject $project): self
   {
       $this->project = $project;
       return $this;
   }

   public function getUserProfile(): ?UserProfileCel
   {
       return $this->userProfile;
   }
   public function setUserProfile(?UserProfileCel $userProfile): self
   {
       $this->userProfile = $userProfile;
       return $this;
   }
   /**
    * @return Collection|Photo[]
    */
   public function getPhotos(): Collection
   {
       return $this->photos;
   }
   public function addPhoto(Photo $photo): self
   {
       if (!$this->photos->contains($photo)) {
           $this->photos[] = $photo;
           $photo->setOccurrence($this);
       }
       return $this;
   }
   public function removePhoto(Photo $photo): self
   {
       if ($this->photos->contains($photo)) {
           $this->photos->removeElement($photo);
           // set the owning side to null (unless already changed)
           if ($photo->getOccurrence() === $this) {
               $photo->setOccurrence(null);
           }
       }
       return $this;
   }




    public function getUserOccurrenceTags(): array
    {
        $tags = array();

        foreach($this->userTagRelations as $rel) {
            $tags[] = $rel;
        }
        return $tags;

    }
/*
    public function addUserTag(UserOccurrenceTag $tag): self
    {
	$em = $this->getDoctrine()->getEntityManager();
        $userTagRelation = new OccurrenceUserOccurrenceTagRelation();
        $userTagRelation->setUserOccurrenceTag($tag);
        $userTagRelation->setOccurrence($this);
        $em->persist($userTagRelation);
        $this->userTagRelations[] = $userTagRelation;
    }

   public function removeUserTag(UserOccurrenceTag $tag): self
   {
        $em = $this->getDoctrine()->getEntityManager();
        foreach($this->userTagRelations as $rel) {
            if ( $rel->getUserOccurrenceTag() ==  $tag ) {
                $em->remove($rel);
                $em->flush();
            }
        }
   }
*/
   /**
    * @return Collection|ExtendedFieldValue[]
    */
   public function getExtendedFieldValues(): Collection
   {
       return $this->extendedFieldValues;
   }

   public function addExtendedFieldValue( $extendedFieldValue): self
   {
       if (!$this->extendedFieldValues->contains($extendedFieldValue)) {
           $this->extendedFieldValues[] = $extendedFieldValue;
           $extendedFieldValue->setOccurrence($this);
        }           
        return $this;
    }

    public function removeExtendedFieldValue( $extendedFieldValue): self
    {
       if ($this->extendedFieldValues->contains($extendedFieldValue)) {
           $this->extendedFieldValues->removeElement($extendedFieldValue);
           // set the owning side to null (unless already changed)
           if ($extendedFieldValue->getOccurrence() === $this) {
               $extendedFieldValue->setOccurrence(null);
           }
       }
       
       return $this;
    }

    public function generateSignature() {
        $unencodedSignature = '';
        $signatureBits = [(string)$this->getUserId(), $this->getDateObservedMonth(), 
                          $this->getDateObservedDay(), $this->getDateObservedYear(), 
                          $this->getUserSciName(), $this->getGeometry(), 
                          $this->getLocality()];

        foreach($signatureBits as $bit) {
            $unencodedSignature = $unencodedSignature . '-' . $bit;
        }

        $this->signature = base64_encode($unencodedSignature);

    }

    public function __clone() {
        if ($this->id) {
            $this->setId(null);
/*
            if ( null !== $this->project ) {
                $this->project = clone $this->project;
            }
            if ( null !== $this->userProfile ) {
                $this->userProfile = clone $this->userProfile;
            }    
*/
        }
    }

    public function __toString()
    {
        $format = "Occurrence (id: %s)\n";
        return sprintf($format, $this->id);
    }

}
