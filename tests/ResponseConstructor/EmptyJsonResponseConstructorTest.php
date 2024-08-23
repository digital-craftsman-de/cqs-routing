<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ResponseConstructor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @coversDefaultClass \DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyJsonResponseConstructor */
final class EmptyJsonResponseConstructorTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::constructResponse
     */
    public function empty_response_constructor_constructs_response(): void
    {
        // -- Arrange
        $emptyJsonResponseConstructor = new EmptyJsonResponseConstructor();

        // -- Act
        $response = $emptyJsonResponseConstructor->constructResponse(null, new Request());

        // -- Assert
        self::assertEmpty($response->getContent());
        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-Type'));
    }
}
