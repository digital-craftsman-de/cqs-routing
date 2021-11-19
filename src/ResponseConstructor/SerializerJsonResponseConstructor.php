<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ResponseConstructor;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class SerializerJsonResponseConstructor implements ResponseConstructorInterface
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    public function constructResponse($data, Request $request): JsonResponse
    {
        $content = $this->serializer->serialize($data, JsonEncoder::FORMAT, [
            // TODO: See if it's possible to move this into a configuration
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
        ]);

        return new JsonResponse($content, Response::HTTP_OK, [], true);
    }
}
