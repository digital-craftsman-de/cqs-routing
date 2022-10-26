<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ResponseConstructor;

use DigitalCraftsman\CQRS\Test\ReadModel\User as UserReadModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/** @coversDefaultClass \DigitalCraftsman\CQRS\ResponseConstructor\SerializerJsonResponseConstructor */
final class SerializerJsonResponseConstructorTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::constructResponse
     */
    public function serializer_json_response_constructor_constructs_response(): void
    {
        // -- Arrange
        $serializerJsonResponseConstructor = new SerializerJsonResponseConstructor(
            new Serializer([new PropertyNormalizer()], [new JsonEncoder()]),
            [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
            ],
        );

        $userReadModel = new UserReadModel(
            'a6077dda-2ace-41e3-ac67-5ebae3a9fabc',
            'Tom Test',
            2000,
            true,
        );

        $expectedJSON = '{"userId":"a6077dda-2ace-41e3-ac67-5ebae3a9fabc","name":"Tom Test","amountPayed":2000,"isEnabled":true}';

        // -- Act
        $response = $serializerJsonResponseConstructor->constructResponse($userReadModel, new Request());

        // -- Assert
        self::assertSame($expectedJSON, $response->getContent());
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-Type'));
    }
}
