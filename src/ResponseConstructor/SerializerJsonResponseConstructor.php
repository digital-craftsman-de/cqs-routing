<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ResponseConstructor;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

final class SerializerJsonResponseConstructor implements ResponseConstructorInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private array $serializerContext,
    ) {
    }

    public function constructResponse($data, Request $request): JsonResponse
    {
        $content = $this->serializer->serialize($data, JsonEncoder::FORMAT, $this->serializerContext);

        return new JsonResponse($content, Response::HTTP_OK, [], true);
    }
}
