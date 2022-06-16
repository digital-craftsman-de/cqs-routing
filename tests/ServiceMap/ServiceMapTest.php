<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\ServiceMap;

use DigitalCraftsman\CQRS\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQRS\ServiceMap\Exception\ConfiguredRequestDecoderNotAvailable;
use DigitalCraftsman\CQRS\ServiceMap\Exception\RequestDecoderOrDefaultRequestDecoderMustBeConfigured;
use DigitalCraftsman\CQRS\Test\RequestDecoder\CreateTaskRequestDecoder;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \DigitalCraftsman\CQRS\ServiceMap\ServiceMap */
final class ServiceMapTest extends TestCase
{
    // -- Request decoders

    /**
     * @test
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_works_with_request_decoder(): void
    {
        // -- Arrange
        $requestDecoders = [
            $createTaskRequestDecoder = new CreateTaskRequestDecoder(),
            new JsonRequestDecoder(),
        ];
        $serviceMap = new ServiceMap($requestDecoders, [], [], [], [], [], [], []);

        // -- Act
        $requestDecoder = $serviceMap->getRequestDecoder(CreateTaskRequestDecoder::class, null);

        // -- Assert
        self::assertSame($createTaskRequestDecoder, $requestDecoder);
    }

    /**
     * @test
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_works_with_default_request_decoder(): void
    {
        // -- Arrange
        $requestDecoders = [
            new CreateTaskRequestDecoder(),
            $defaultRequestDecoder = new JsonRequestDecoder(),
        ];
        $serviceMap = new ServiceMap($requestDecoders, [], [], [], [], [], [], []);

        // -- Act
        $requestDecoder = $serviceMap->getRequestDecoder(null, JsonRequestDecoder::class);

        // -- Assert
        self::assertSame($defaultRequestDecoder, $requestDecoder);
    }

    /**
     * @test
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_fails_when_request_decoder_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredRequestDecoderNotAvailable::class);

        // -- Arrange
        $requestDecoders = [
            new JsonRequestDecoder(),
        ];
        $serviceMap = new ServiceMap($requestDecoders, [], [], [], [], [], [], []);

        // -- Act
        $serviceMap->getRequestDecoder(CreateTaskRequestDecoder::class, null);
    }

    /**
     * @test
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_fails_when_default_request_decoder_is_not_available(): void
    {
        // -- Assert
        $this->expectException(ConfiguredRequestDecoderNotAvailable::class);

        // -- Arrange
        $requestDecoders = [
            new CreateTaskRequestDecoder(),
        ];
        $serviceMap = new ServiceMap($requestDecoders, [], [], [], [], [], [], []);

        // -- Act
        $serviceMap->getRequestDecoder(null, JsonRequestDecoder::class);
    }

    /**
     * @test
     * @covers ::getRequestDecoder
     */
    public function get_request_decoder_fails_when_no_request_decoder_and_default_request_decoder_is_defined(): void
    {
        // -- Assert
        $this->expectException(RequestDecoderOrDefaultRequestDecoderMustBeConfigured::class);

        // -- Arrange
        $serviceMap = new ServiceMap([], [], [], [], [], [], [], []);

        // -- Act
        $serviceMap->getRequestDecoder(null, null);
    }
}
