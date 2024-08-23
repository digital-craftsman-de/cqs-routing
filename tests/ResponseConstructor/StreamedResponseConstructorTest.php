<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ResponseConstructor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @coversDefaultClass \DigitalCraftsman\CQSRouting\ResponseConstructor\StreamedResponseConstructor */
final class StreamedResponseConstructorTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::constructResponse
     */
    public function streamed_response_constructor_constructs_response(): void
    {
        // -- Arrange
        $streamedResponseConstructor = new StreamedResponseConstructor();

        // -- Act
        $response = $streamedResponseConstructor->constructResponse(fn () => true, new Request());

        // -- Assert
        self::assertEmpty($response->getContent());
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
