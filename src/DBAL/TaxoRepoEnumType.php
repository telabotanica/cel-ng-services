<?php

namespace App\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * ENUM DB type for allowed taxonomic repository values. 






La ressource 'BDTFX' n'indique pas un projet existant. Les projets existant sont : bdtfxr, aublet, florical, bdtre, commun, sophy, apd, sptba, nvps, bdnt, bdtfx, bdtxa, chorodep, coste, eflore, fournier, insee-d, iso-3166-1, iso-639-1, nvjfl, cel, lion1906, liste-rouge, wikipedia, osm, prometheus, bibliobota, photoflora, baseflor, baseveg, sptb, isfan, nva, moissonnage, nasa-srtm, coord-transfo, lbf




 */
class TaxoRepoEnumType extends Type
{
    const TAXO_REPO_ENUM = 'taxorepoenum';
    const BDTFE = 'bdtfe';
    const BDTFX = 'bdtfx';
    const TAXREF = 'taxref';
    const BDTFER = 'bdtfer';
    const VASCAN = 'vascan';
    const APD = 'apd';
    const LBF = 'lbf';
    const OTHERUNKNOWN = 'Autre/inconnu';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('bdtfe', 'bdtfx', 'taxref', 'bdtfer', 'vascan', 'apd', 'lbf', 'Autre/inconnu')";
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(null, self::BDTFE, self::BDTFX, self::TAXREF, self::BDTFER, self::VASCAN, self::APD, self::LBF, self::OTHERUNKNOWN))) {
            throw new \InvalidArgumentException("Invalid taxo repository");
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::TAXO_REPO_ENUM;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

}
