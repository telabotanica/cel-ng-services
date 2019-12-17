<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;


class JsonPatchOccurrenceEncoder implements EncoderInterface, DecoderInterface {
    /**
     * @inheritdoc
     */
    public function encode($data, $format, array $context = array()) {
        return json_encode($data);
    }

    /**
     * @inheritdoc
     */
    public function supportsEncoding($format) {
        return 'jsonpatch' === $format;
    }

    /**
     * @inheritdoc
     */
    public function decode($data, $format, array $context = array()) {
        return json_decode($data);
    }

    /**
     * @inheritdoc
     */
    public function supportsDecoding($format) {
        return 'jsonpatch' === $format;
    }
}
