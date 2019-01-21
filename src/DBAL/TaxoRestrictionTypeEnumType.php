<?php

namespace App\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * ENUM type for allowed WidgetConfiguration taxo restriction type values. 
 */
class TaxoRestrictionTypeEnumType extends Type
{
    const TAXO_RESTRICTION_TYPE_ENUM = 'taxorestrictiontypeenum';
    const TAXON = 'taxon';
    const TAXA = 'taxa';
    const REPO = 'repository';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('taxon', 'taxa', 'repository')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(null, self::TAXON, self::TAXA, self::REPO))) {
            throw new \InvalidArgumentException("Invalid taxo restriction type");
        }
        return $value;
    }

    public function getName()
    {
        return self::TAXO_RESTRICTION_TYPE_ENUM;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
