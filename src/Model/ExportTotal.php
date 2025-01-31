<?php

namespace App\Model;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}},
 *     formats={"json"}
 * )
 */
class ExportTotal
{
    /**
     * @var int
     *
     * @Groups({"read"})
     *
     * @ApiProperty(identifier=true)
     */
    private $id_observation;

    /**
     * @var string
     *
     * @Groups({"read"})
     */
    private $guid;

    /**
     * @var bool
     *
     * @Groups({"read"})
     */
    private $donnees_standard;

    /**
     * @var bool
     *
     * @Groups({"read"})
     */
    private $transmission;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $id_plantnet;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $ce_utilisateur;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $pseudo_utilisateur;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $courriel_utilisateur;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $nom_sel;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $nom_sel_nn;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $nom_ret;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $nom_ret_nn;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $famille;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $nom_referentiel;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $pays;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $ce_zone_geo;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $dept;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $zone_geo;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $lieudit;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $station;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $milieu;

    /**
     * @var float|null
     *
     * @Groups({"read"})
     */
    private $latitude;

    /**
     * @var float|null
     *
     * @Groups({"read"})
     */
    private $longitude;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $altitude;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $geodatum;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $geometry;

    /**
     * @var float|null
     *
     */
    private $lat_prive;

    /**
     * @var float|null
     *
     */
    private $long_prive;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $localisation_precision;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $localisation_floutage;

    /**
     * @var bool|null
     *
     * @Groups({"read"})
     */
    private $localisation_coherence;

    /**
     * @var \DateTime|null
     *
     * @Groups({"read"})
     */
    private $date_observation;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $programme;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $mots_cles_texte;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $commentaire;

    /**
     * @var \DateTime|null
     *
     * @Groups({"read"})
     */
    private $date_creation;

    /**
     * @var \DateTime|null
     *
     * @Groups({"read"})
     */
    private $date_modification;

    /**
     * @var \DateTime|null
     *
     * @Groups({"read"})
     */
    private $date_transmission;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $abondance;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $certitude;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $phenologie;

    /**
     * @var bool|null
     *
     * @Groups({"read"})
     */
    private $spontaneite;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $observateur;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $observateur_structure;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $type_donnees;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $biblio;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $source;

    /**
     * @var bool|null
     *
     * @Groups({"read"})
     */
    private $herbier;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $determinateur;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $url_identiplante;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $validation_identiplante;

    /**
     * @var \DateTime|null
     *
     * @Groups({"read"})
     */
    private $date_validation;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $score_identiplante;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     */
    private $images;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $cd_nom;

    /**
     * @var int|null
     *
     * @Groups({"read"})
     */
    private $grade;

    /**
     * @param int $id_observation
     * @param string $guid
     * @param bool $donnees_standard
     * @param bool $transmission
     * @param int|null $id_plantnet
     * @param int|null $ce_utilisateur
     * @param string|null $pseudo_utilisateur
     * @param string|null $courriel_utilisateur
     * @param string|null $nom_sel
     * @param int|null $nom_sel_nn
     * @param string|null $nom_ret
     * @param int|null $nom_ret_nn
     * @param string|null $famille
     * @param string|null $nom_referentiel
     * @param string|null $pays
     * @param string|null $ce_zone_geo
     * @param string|null $dept
     * @param string|null $zone_geo
     * @param string|null $lieudit
     * @param string|null $station
     * @param string|null $milieu
     * @param float|null $latitude
     * @param float|null $longitude
     * @param int|null $altitude
     * @param string|null $geodatum
     * @param string|null $geometry
     * @param float|null $lat_prive
     * @param float|null $long_prive
     * @param string|null $localisation_precision
     * @param string|null $localisation_floutage
     * @param bool|null $localisation_coherence
     * @param \DateTime|null $date_observation
     * @param string|null $programme
     * @param string|null $mots_cles_texte
     * @param string|null $commentaire
     * @param \DateTime|null $date_creation
     * @param \DateTime|null $date_modification
     * @param \DateTime|null $date_transmission
     * @param string|null $abondance
     * @param string|null $certitude
     * @param string|null $phenologie
     * @param bool|null $spontaneite
     * @param string|null $observateur
     * @param string|null $observateur_structure
     * @param string|null $type_donnees
     * @param string|null $biblio
     * @param string|null $source
     * @param bool|null $herbier
     * @param string|null $determinateur
     * @param string|null $url_identiplante
     * @param int|null $validation_identiplante
     * @param \DateTime|null $date_validation
     * @param int|null $score_identiplante
     * @param string|null $images
     * @param int|null $cd_nom
     * @param int|null $grade
     */
    public function __construct(int $id_observation, string $guid, bool $donnees_standard, bool $transmission, ?int $id_plantnet, ?int $ce_utilisateur, ?string $pseudo_utilisateur, ?string $courriel_utilisateur, ?string $nom_sel, ?int $nom_sel_nn, ?string $nom_ret, ?int $nom_ret_nn, ?string $famille, ?string $nom_referentiel, ?string $pays, ?string $ce_zone_geo, ?string $dept, ?string $zone_geo, ?string $lieudit, ?string $station, ?string $milieu, ?float $latitude, ?float $longitude, ?int $altitude, ?string $geodatum, ?string $geometry, ?float $lat_prive, ?float $long_prive, ?string $localisation_precision, ?string $localisation_floutage, ?bool $localisation_coherence, ?\DateTime $date_observation, ?string $programme, ?string $mots_cles_texte, ?string $commentaire, ?\DateTime $date_creation, ?\DateTime $date_modification, ?\DateTime $date_transmission, ?string $abondance, ?string $certitude, ?string $phenologie, ?bool $spontaneite, ?string $observateur, ?string $observateur_structure, ?string $type_donnees, ?string $biblio, ?string $source, ?bool $herbier, ?string $determinateur, ?string $url_identiplante, ?int $validation_identiplante, ?\DateTime $date_validation, ?int $score_identiplante, ?string $images, ?int $cd_nom, ?int $grade)
    {
        $this->id_observation = $id_observation;
        $this->guid = $guid;
        $this->donnees_standard = $donnees_standard;
        $this->transmission = $transmission;
        $this->id_plantnet = $id_plantnet;
        $this->ce_utilisateur = $ce_utilisateur;
        $this->pseudo_utilisateur = $pseudo_utilisateur;
        $this->courriel_utilisateur = $courriel_utilisateur;
        $this->nom_sel = $nom_sel;
        $this->nom_sel_nn = $nom_sel_nn;
        $this->nom_ret = $nom_ret;
        $this->nom_ret_nn = $nom_ret_nn;
        $this->famille = $famille;
        $this->nom_referentiel = $nom_referentiel;
        $this->pays = $pays;
        $this->ce_zone_geo = $ce_zone_geo;
        $this->dept = $dept;
        $this->zone_geo = $zone_geo;
        $this->lieudit = $lieudit;
        $this->station = $station;
        $this->milieu = $milieu;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->altitude = $altitude;
        $this->geodatum = $geodatum;
        $this->geometry = $geometry;
        $this->lat_prive = $lat_prive;
        $this->long_prive = $long_prive;
        $this->localisation_precision = $localisation_precision;
        $this->localisation_floutage = $localisation_floutage;
        $this->localisation_coherence = $localisation_coherence;
        $this->date_observation = $date_observation;
        $this->programme = $programme;
        $this->mots_cles_texte = $mots_cles_texte;
        $this->commentaire = $commentaire;
        $this->date_creation = $date_creation;
        $this->date_modification = $date_modification;
        $this->date_transmission = $date_transmission;
        $this->abondance = $abondance;
        $this->certitude = $certitude;
        $this->phenologie = $phenologie;
        $this->spontaneite = $spontaneite;
        $this->observateur = $observateur;
        $this->observateur_structure = $observateur_structure;
        $this->type_donnees = $type_donnees;
        $this->biblio = $biblio;
        $this->source = $source;
        $this->herbier = $herbier;
        $this->determinateur = $determinateur;
        $this->url_identiplante = $url_identiplante;
        $this->validation_identiplante = $validation_identiplante;
        $this->date_validation = $date_validation;
        $this->score_identiplante = $score_identiplante;
        $this->images = $images;
        $this->cd_nom = $cd_nom;
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
        return $this->id_observation;
    }

    /**
     * @return int
     */
    public function getIdObservation(): int
    {
        return $this->id_observation;
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
    public function isDonneesStandard(): bool
    {
        return $this->donnees_standard;
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
        return $this->id_plantnet;
    }

    /**
     * @return int|null
     */
    public function getCeUtilisateur(): ?int
    {
        return $this->ce_utilisateur;
    }

    /**
     * @return string|null
     */
    public function getPseudoUtilisateur(): ?string
    {
        return $this->pseudo_utilisateur;
    }

    /**
     * @return string|null
     */
    public function getCourrielUtilisateur(): ?string
    {
        return $this->courriel_utilisateur;
    }

    /**
     * @return string|null
     */
    public function getNomSel(): ?string
    {
        return $this->nom_sel;
    }

    /**
     * @return int|null
     */
    public function getNomSelNn(): ?int
    {
        return $this->nom_sel_nn;
    }

    /**
     * @return string|null
     */
    public function getNomRet(): ?string
    {
        return $this->nom_ret;
    }

    /**
     * @return int|null
     */
    public function getNomRetNn(): ?int
    {
        return $this->nom_ret_nn;
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
    public function getNomReferentiel(): ?string
    {
        return $this->nom_referentiel;
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
        return $this->ce_zone_geo;
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
        return $this->zone_geo;
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
        return $this->lat_prive;
    }

    /**
     * @return float|null
     */
    public function getLongPrive(): ?float
    {
        return $this->long_prive;
    }

    /**
     * @return string|null
     */
    public function getLocalisationPrecision(): ?string
    {
        return $this->localisation_precision;
    }

    /**
     * @return string|null
     */
    public function getLocalisationFloutage(): ?string
    {
        return $this->localisation_floutage;
    }

    /**
     * @return bool|null
     */
    public function getLocalisationCoherence(): ?bool
    {
        return $this->localisation_coherence;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateObservation(): ?\DateTime
    {
        return $this->date_observation;
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
        return $this->mots_cles_texte;
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
        return $this->date_creation;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateModification(): ?\DateTime
    {
        return $this->date_modification;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTransmission(): ?\DateTime
    {
        return $this->date_transmission;
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
        return $this->observateur_structure;
    }

    /**
     * @return string|null
     */
    public function getTypeDonnees(): ?string
    {
        return $this->type_donnees;
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
        return $this->url_identiplante;
    }

    /**
     * @return int|null
     */
    public function getValidationIdentiplante(): ?int
    {
        return $this->validation_identiplante;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateValidation(): ?\DateTime
    {
        return $this->date_validation;
    }

    /**
     * @return int|null
     */
    public function getScoreIdentiplante(): ?int
    {
        return $this->score_identiplante;
    }

    /**
     * @return string|null
     */
    public function getImages(): ?string
    {
        return $this->images;
    }

    /**
     * @return int|null
     */
    public function getCdNom(): ?int
    {
        return $this->cd_nom;
    }

    /**
     * @return int|null
     */
    public function getGrade(): ?int
    {
        return $this->grade;
    }


}
