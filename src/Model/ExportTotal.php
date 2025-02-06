<?php

namespace App\Model;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use App\filter\ExportTotalFilter;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ApiResource(
 *     description="Export Total des obs publics avec image",
 *     collectionOperations={
 *         "get"={
 *             "method"="GET",
 *             "swagger_context"={
 *                 "summary"="Récupère la liste des exports avec filtres",
 *                 "description"="Cette route permet de récupérer la liste des exports des observations publiques avec des options de pagination et de tri.",
 *             }
 *         }
 *     },
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"exportTotal_read"}},
 *     denormalizationContext={"groups"={"exportTotal_write"}},
 *     formats={"json"},
 *     attributes={"pagination_enabled"=false}
 * )
 * @ApiFilter(ExportTotalFilter::class)
 */
class ExportTotal
{
    /**
     * @var int
     *
     * @Groups({"exportTotal_read"})
     */
    private $idObservation;

    /**
     * @var string
     *
     * @Groups({"exportTotal_read"})
     */
    private $guid;

    /**
     * @var bool
     * @Groups({"exportTotal_read"})
     */
    private $donneesStandard;

    /**
     * @var bool
     *
     * @Groups({"exportTotal_read"})
     */
    private $transmission;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $idPlantnet;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $ceUtilisateur;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $pseudoUtilisateur;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $courrielUtilisateur;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $nomSel;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $nomSelNn;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $nomRet;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $nomRetNn;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $famille;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $referentiel;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $pays;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $ceZoneGeo;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $dept;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $zoneGeo;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $lieudit;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $station;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $milieu;

    /**
     * @var float|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $latitude;

    /**
     * @var float|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $longitude;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $altitude;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $geodatum;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $geometry;

    /**
     * @var float|null
     *
     */
    private $latPrive;

    /**
     * @var float|null
     *
     */
    private $longPrive;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $localisationPrecise;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $localisationFloutage;

    /**
     * @var bool|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $localisationCoherence;

    /**
     * @var \DateTime|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $dateObservation;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $programme;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $motsClesTexte;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $commentaire;

    /**
     * @var \DateTime|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $dateCreation;

    /**
     * @var \DateTime|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $dateModification;

    /**
     * @var \DateTime|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $dateTransmission;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $abondance;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $certitude;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $phenologie;

    /**
     * @var bool|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $spontaneite;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $observateur;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $observateurStructure;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $typeDonnees;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $biblio;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $source;

    /**
     * @var bool|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $herbier;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $determinateur;

    /**
     * @var string|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $urlIdentiplante;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $validationIdentiplante;

    /**
     * @var \DateTime|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $dateValidation;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $scoreIdentiplante;

    /**
     * @var array|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $images;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $cdcNom;

    /**
     * @var int|null
     *
     * @Groups({"exportTotal_read"})
     */
    private $grade;

    /**
     * @param int $idObservation
     * @param string $guid
     * @param bool $donneesStandard
     * @param bool $transmission
     * @param int|null $idPlantnet
     * @param int|null $ceUtilisateur
     * @param string|null $pseudoUtilisateur
     * @param string|null $courrielUtilisateur
     * @param string|null $nomSel
     * @param int|null $nomSelNn
     * @param string|null $nomRet
     * @param int|null $nomRetNn
     * @param string|null $famille
     * @param string|null $referentiel
     * @param string|null $pays
     * @param string|null $ceZoneGeo
     * @param string|null $dept
     * @param string|null $zoneGeo
     * @param string|null $lieudit
     * @param string|null $station
     * @param string|null $milieu
     * @param float|null $latitude
     * @param float|null $longitude
     * @param int|null $altitude
     * @param string|null $geodatum
     * @param string|null $geometry
     * @param float|null $latPrive
     * @param float|null $longPrive
     * @param string|null $localisationPrecise
     * @param string|null $localisationFloutage
     * @param bool|null $localisationCoherence
     * @param \DateTime|null $dateObservation
     * @param string|null $programme
     * @param string|null $motsClesTexte
     * @param string|null $commentaire
     * @param \DateTime|null $dateCreation
     * @param \DateTime|null $dateModification
     * @param \DateTime|null $dateTransmission
     * @param string|null $abondance
     * @param string|null $certitude
     * @param string|null $phenologie
     * @param bool|null $spontaneite
     * @param string|null $observateur
     * @param string|null $observateurStructure
     * @param string|null $typeDonnees
     * @param string|null $biblio
     * @param string|null $source
     * @param bool|null $herbier
     * @param string|null $determinateur
     * @param string|null $urlIdentiplante
     * @param int|null $validationIdentiplante
     * @param \DateTime|null $dateValidation
     * @param int|null $scoreIdentiplante
     * @param array|null $images
     * @param int|null $cdcNom
     * @param int|null $grade
     */
    public function __construct(int $idObservation, string $guid, bool $donneesStandard, bool $transmission, ?int $idPlantnet, ?int $ceUtilisateur, ?string $pseudoUtilisateur, ?string $courrielUtilisateur, ?string $nomSel, ?int $nomSelNn, ?string $nomRet, ?int $nomRetNn, ?string $famille, ?string $referentiel, ?string $pays, ?string $ceZoneGeo, ?string $dept, ?string $zoneGeo, ?string $lieudit, ?string $station, ?string $milieu, ?float $latitude, ?float $longitude, ?int $altitude, ?string $geodatum, ?string $geometry, ?float $latPrive, ?float $longPrive, ?string $localisationPrecise, ?string $localisationFloutage, ?bool $localisationCoherence, ?\DateTime $dateObservation, ?string $programme, ?string $motsClesTexte, ?string $commentaire, ?\DateTime $dateCreation, ?\DateTime $dateModification, ?\DateTime $dateTransmission, ?string $abondance, ?string $certitude, ?string $phenologie, ?bool $spontaneite, ?string $observateur, ?string $observateurStructure, ?string $typeDonnees, ?string $biblio, ?string $source, ?bool $herbier, ?string $determinateur, ?string $urlIdentiplante, ?int $validationIdentiplante, ?\DateTime $dateValidation, ?int $scoreIdentiplante, ?array $images, ?int $cdcNom, ?int $grade)
    {
        $this->idObservation = $idObservation;
        $this->guid = $guid;
        $this->donneesStandard = $donneesStandard;
        $this->transmission = $transmission;
        $this->idPlantnet = $idPlantnet;
        $this->ceUtilisateur = $ceUtilisateur;
        $this->pseudoUtilisateur = $pseudoUtilisateur;
        $this->courrielUtilisateur = $courrielUtilisateur;
        $this->nomSel = $nomSel;
        $this->nomSelNn = $nomSelNn;
        $this->nomRet = $nomRet;
        $this->nomRetNn = $nomRetNn;
        $this->famille = $famille;
        $this->referentiel = $referentiel;
        $this->pays = $pays;
        $this->ceZoneGeo = $ceZoneGeo;
        $this->dept = $dept;
        $this->zoneGeo = $zoneGeo;
        $this->lieudit = $lieudit;
        $this->station = $station;
        $this->milieu = $milieu;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->altitude = $altitude;
        $this->geodatum = $geodatum;
        $this->geometry = $geometry;
        $this->latPrive = $latPrive;
        $this->longPrive = $longPrive;
        $this->localisationPrecise = $localisationPrecise;
        $this->localisationFloutage = $localisationFloutage;
        $this->localisationCoherence = $localisationCoherence;
        $this->dateObservation = $dateObservation;
        $this->programme = $programme;
        $this->motsClesTexte = $motsClesTexte;
        $this->commentaire = $commentaire;
        $this->dateCreation = $dateCreation;
        $this->dateModification = $dateModification;
        $this->dateTransmission = $dateTransmission;
        $this->abondance = $abondance;
        $this->certitude = $certitude;
        $this->phenologie = $phenologie;
        $this->spontaneite = $spontaneite;
        $this->observateur = $observateur;
        $this->observateurStructure = $observateurStructure;
        $this->typeDonnees = $typeDonnees;
        $this->biblio = $biblio;
        $this->source = $source;
        $this->herbier = $herbier;
        $this->determinateur = $determinateur;
        $this->urlIdentiplante = $urlIdentiplante;
        $this->validationIdentiplante = $validationIdentiplante;
        $this->dateValidation = $dateValidation;
        $this->scoreIdentiplante = $scoreIdentiplante;
        $this->images = $images;
        $this->cdcNom = $cdcNom;
        $this->grade = $grade;
    }

    /**
     * Méthode d'identification utilisée par API Platform
     *
     * @return int
     *
     * @ApiProperty(identifier=true)
     */
    public function getId(): int
    {
        return $this->idObservation;
    }

    /**
     * @return int
     */
    public function getIdObservation(): int
    {
        return $this->idObservation;
    }

    /**
     * @return string
     */
    public function getGuid(): string
    {
        return $this->guid;
    }

    /**
     * @return bool
     */
    public function getDonneesStandard(): bool
    {
        return $this->donneesStandard;
    }

    /**
     * @return bool
     */
    public function isTransmission(): bool
    {
        return $this->transmission;
    }

    /**
     * @return int|null
     */
    public function getIdPlantnet(): ?int
    {
        return $this->idPlantnet;
    }

    /**
     * @return int|null
     */
    public function getCeUtilisateur(): ?int
    {
        return $this->ceUtilisateur;
    }

    /**
     * @return string|null
     */
    public function getPseudoUtilisateur(): ?string
    {
        return $this->pseudoUtilisateur;
    }

    /**
     * @return string|null
     */
    public function getCourrielUtilisateur(): ?string
    {
        return $this->courrielUtilisateur;
    }

    /**
     * @return string|null
     */
    public function getNomSel(): ?string
    {
        return $this->nomSel;
    }

    /**
     * @return int|null
     */
    public function getNomSelNn(): ?int
    {
        return $this->nomSelNn;
    }

    /**
     * @return string|null
     */
    public function getNomRet(): ?string
    {
        return $this->nomRet;
    }

    /**
     * @return int|null
     */
    public function getNomRetNn(): ?int
    {
        return $this->nomRetNn;
    }

    /**
     * @return string|null
     */
    public function getFamille(): ?string
    {
        return $this->famille;
    }

    /**
     * @return string|null
     */
    public function getReferentiel(): ?string
    {
        return $this->referentiel;
    }

    /**
     * @return string|null
     */
    public function getPays(): ?string
    {
        return $this->pays;
    }

    /**
     * @return string|null
     */
    public function getCeZoneGeo(): ?string
    {
        return $this->ceZoneGeo;
    }

    /**
     * @return string|null
     */
    public function getDept(): ?string
    {
        return $this->dept;
    }

    /**
     * @return string|null
     */
    public function getZoneGeo(): ?string
    {
        return $this->zoneGeo;
    }

    /**
     * @return string|null
     */
    public function getLieudit(): ?string
    {
        return $this->lieudit;
    }

    /**
     * @return string|null
     */
    public function getStation(): ?string
    {
        return $this->station;
    }

    /**
     * @return string|null
     */
    public function getMilieu(): ?string
    {
        return $this->milieu;
    }

    /**
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @return int|null
     */
    public function getAltitude(): ?int
    {
        return $this->altitude;
    }

    /**
     * @return string|null
     */
    public function getGeodatum(): ?string
    {
        return $this->geodatum;
    }

    /**
     * @return string|null
     */
    public function getGeometry(): ?string
    {
        return $this->geometry;
    }

    /**
     * @return float|null
     */
    public function getLatPrive(): ?float
    {
        return $this->latPrive;
    }

    /**
     * @return float|null
     */
    public function getLongPrive(): ?float
    {
        return $this->longPrive;
    }

    /**
     * @return string|null
     */
    public function getLocalisationPrecision(): ?string
    {
        return $this->localisationPrecise;
    }

    /**
     * @return string|null
     */
    public function getLocalisationFloutage(): ?string
    {
        return $this->localisationFloutage;
    }

    /**
     * @return bool|null
     */
    public function getLocalisationCoherence(): ?bool
    {
        return $this->localisationCoherence;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateObservation(): ?\DateTime
    {
        return $this->dateObservation;
    }

    /**
     * @return string|null
     */
    public function getProgramme(): ?string
    {
        return $this->programme;
    }

    /**
     * @return string|null
     */
    public function getMotsClesTexte(): ?string
    {
        return $this->motsClesTexte;
    }

    /**
     * @return string|null
     */
    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateModification(): ?\DateTime
    {
        return $this->dateModification;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTransmission(): ?\DateTime
    {
        return $this->dateTransmission;
    }

    /**
     * @return string|null
     */
    public function getAbondance(): ?string
    {
        return $this->abondance;
    }

    /**
     * @return string|null
     */
    public function getCertitude(): ?string
    {
        return $this->certitude;
    }

    /**
     * @return string|null
     */
    public function getPhenologie(): ?string
    {
        return $this->phenologie;
    }

    /**
     * @return bool|null
     */
    public function getSpontaneite(): ?bool
    {
        return $this->spontaneite;
    }

    /**
     * @return string|null
     */
    public function getObservateur(): ?string
    {
        return $this->observateur;
    }

    /**
     * @return string|null
     */
    public function getObservateurStructure(): ?string
    {
        return $this->observateurStructure;
    }

    /**
     * @return string|null
     */
    public function getTypeDonnees(): ?string
    {
        return $this->typeDonnees;
    }

    /**
     * @return string|null
     */
    public function getBiblio(): ?string
    {
        return $this->biblio;
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @return bool|null
     */
    public function getHerbier(): ?bool
    {
        return $this->herbier;
    }

    /**
     * @return string|null
     */
    public function getDeterminateur(): ?string
    {
        return $this->determinateur;
    }

    /**
     * @return string|null
     */
    public function getUrlIdentiplante(): ?string
    {
        return $this->urlIdentiplante;
    }

    /**
     * @return int|null
     */
    public function getValidationIdentiplante(): ?int
    {
        return $this->validationIdentiplante;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateValidation(): ?\DateTime
    {
        return $this->dateValidation;
    }

    /**
     * @return int|null
     */
    public function getScoreIdentiplante(): ?int
    {
        return $this->scoreIdentiplante;
    }

    /**
     * @return array|null
     */
    public function getImages(): ?array
    {
        return $this->images;
    }

    /**
     * @return int|null
     */
    public function getCdNom(): ?int
    {
        return $this->cdcNom;
    }

    /**
     * @return int|null
     */
    public function getGrade(): ?int
    {
        return $this->grade;
    }


}
