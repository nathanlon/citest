<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ApiRequestSerializer extends Serializer
{
    public function __construct(
        public array $normalizers = [
            new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())
        ],
        public array $encoders = [
            new JsonEncoder()
        ]
    ) {
        parent::__construct($normalizers, $encoders);
    }
}
