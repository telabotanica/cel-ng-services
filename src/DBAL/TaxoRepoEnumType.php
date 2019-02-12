<?php

namespace App\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * ENUM DB type for allowed taxonomic repository values. 
 *
 * @package App\DBAL
 * @refactor: DRY all DBAL types using a superclass, children will only have 
 *            a constant associative array, a type_enum and an error msg.
 */
class TaxoRepoEnumType extends Type {

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
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
 
        return "ENUM('bdtfe', 'bdtfx', 'taxref', 'bdtfer', 'vascan', 'apd', 'lbf', 'Autre/inconnu')";
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform) {
 
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
 
        if (!in_array($value, array(null, self::BDTFE, self::BDTFX, self::TAXREF, self::BDTFER, self::VASCAN, self::APD, self::LBF, self::OTHERUNKNOWN))) {
            throw new \InvalidArgumentException("Invalid taxo repository");
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
 
        return self::TAXO_REPO_ENUM;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform) {
 
        return true;
    }

}
