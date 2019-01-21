<?php

namespace App\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class PublishedLocationEnumType extends Type
{
    const PUBLISHED_LOCATION_ENUM = 'publishedlocationenum';
    const PRECISE = 'précise';
    const LOCALITY = 'localité';
    const TEN_BY_TEN = '10x10km';


    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('précise', 'localité', '10x10km')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(null, self::PRECISE, self::LOCALITY, self::TEN_BY_TEN))) {
            throw new \InvalidArgumentException("Invalid published loocation value");
        }
        return $value;
    }

    public function getName()
    {
        return self::PUBLISHED_LOCATION_ENUM;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
