<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ResponseConstructor;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(StreamedResponseConstructor::class)]
final class StreamedResponseConstructorTest extends TestCase
{
    #[Test]
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
