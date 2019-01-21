<?php

namespace App\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * ENUM type for allowed Taxo identification certainty values. 
 * @todo fill it with the values to be sent by Laura
 */
class CertaintyEnumType extends Type
{
    const CERTAINTY_ENUM = 'certaintyenum';
    const TO_BE_DETERMINED = 'à déterminer';
    const DOUBTFUL = 'douteux';
    const CERTAIN = 'certain';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('à déterminer', 'douteux', 'certain')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(null, self::DOUBTFUL, self::CERTAIN, self::TO_BE_DETERMINED))) {
            throw new \InvalidArgumentException("Invalid certainty value");
        }
        return $value;
    }

    public function getName()
    {
        return self::CERTAINTY_ENUM;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
