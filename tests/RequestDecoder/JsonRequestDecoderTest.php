<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\RequestDecoder;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/** @coversDefaultClass \DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder */
final class JsonRequestDecoderTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::decodeRequest
     */
    public function json_request_decoder_decodes_json(): void
    {
        // -- Arrange
        $jsonRequestDecoder = new JsonRequestDecoder();
        $json = '{
            "userId": "abf6b545-951e-46d8-b444-dc57b31ee51f",
            "amount": 2000,
            "isRelevant": true
        }';
        $request = new Request(
            content: $json,
        );

        $expectedRequestData = [
            'userId' => 'abf6b545-951e-46d8-b444-dc57b31ee51f',
            'amount' => 2000,
            'isRelevant' => true,
        ];

        // -- Act
        $requestData = $jsonRequestDecoder->decodeRequest($request);

        // -- Assert
        self::assertSame($expectedRequestData, $requestData);
    }
}
