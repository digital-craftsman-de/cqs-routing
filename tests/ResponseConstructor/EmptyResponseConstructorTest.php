<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\ResponseConstructor;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(EmptyResponseConstructor::class)]
final class EmptyResponseConstructorTest extends TestCase
{
    #[Test]
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
