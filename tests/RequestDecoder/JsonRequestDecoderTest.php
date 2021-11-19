<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\RequestDecoder;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class JsonRequestDecoderTest extends TestCase
{
    /** @test */
    public function json_request_decoder_decodes_json(): void
    {
        // Arrange
        $jsonRequestDecoder = new JsonRequestDecoder();
        $json = '{
            "userId": "abf6b545-951e-46d8-b444-dc57b31ee51f",
            "amount": 2000,
            "isRelevant": true
        }';
        $request = new Request(
            content: $json,
        );

        $expectedDTOData = [
            'userId' => 'abf6b545-951e-46d8-b444-dc57b31ee51f',
            'amount' => 2000,
            'isRelevant' => true,
        ];

        // Act
        $dtoData = $jsonRequestDecoder->decodeRequest($request);

        // Assert
        self::assertSame($expectedDTOData, $dtoData);
    }
}
