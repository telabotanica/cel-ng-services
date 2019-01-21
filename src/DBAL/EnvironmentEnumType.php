<?php

namespace App\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * ENUM type for allowed WidgetConfiguration entity localisation type values. 
 * @todo fill it with the values to be sent by Laura
 */
class EnvironmentEnumType extends Type
{
    const ENV_TYPE_ENUM = 'environmentenum';
    const BDTFE = 'BDTFE';
    const INDEXREDUIT = 'index_reduit';
    const BDTXA = 'BDTXA';
    const BDTRE = 'BDTRE';
    const FLORICAL = 'FLORICAL';
    const AUBLET2 = 'AUBLET2';
    const ISPAN = 'ISPAN';
    const APD = 'APD';
    const LBF = 'LBF';
    const OTHERUNKNOWN = 'Autre/inconnu';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('BDTFE', 'index_reduit', 'BDTXA', 'BDTRE', 'FLORICAL', 'AUBLET2', 'ISPAN', 'APD', 'LBF', 'Autre/inconnu')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(null, self::BDTFE, self::INDEXREDUIT, self::BDTXA, self::BDTRE, self::FLORICAL, self::AUBLET2, self::ISPAN, self::APD, self::LBF, self::OTHERUNKNOWN))) {
            throw new \InvalidArgumentException("Invalid localisation type");
        }
        return $value;
    }

    public function getName()
    {
        return self::ENV_TYPE_ENUM;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
