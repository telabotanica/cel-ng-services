<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MicroDateTimeNormalizer implements DenormalizerInterface
{
    private static $supportedTypes = [
        \DateTime::class => true,
    ];

    /**
     * {@inheritdoc}
     *
     * @throws NotNormalizableValueException
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $timestamp = substr($data, 0, -3);
        return \DateTime::createFromFormat('U', $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return isset(self::$supportedTypes[$type]);
    }
}
