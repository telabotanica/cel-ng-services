<?php

namespace App\Serializer;

use App\Model\PlantnetMicroDateTime;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PlantnetMicroDateTimeNormalizer implements DenormalizerInterface
{
    private static $supportedTypes = [
        PlantnetMicroDateTime::class => true,
    ];

    /**
     * {@inheritdoc}
     *
     * @throws NotNormalizableValueException
     */
    public function denormalize($data, $class, $format = null, array $context = []) {
        $timestamp = substr($data, 0, -3);
        return \DateTime::createFromFormat('U', $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null) {
        return isset(self::$supportedTypes[$type]);
    }
}
