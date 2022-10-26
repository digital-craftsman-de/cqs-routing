<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ResponseConstructor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @coversDefaultClass \DigitalCraftsman\CQRS\ResponseConstructor\EmptyResponseConstructor */
final class EmptyResponseConstructorTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::constructResponse
     */
    public function empty_response_constructor_constructs_response(): void
    {
        // -- Arrange
        $emptyResponseConstructor = new EmptyResponseConstructor();

        // -- Act
        $response = $emptyResponseConstructor->constructResponse(null, new Request());

        // -- Assert
        self::assertEmpty($response->getContent());
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
