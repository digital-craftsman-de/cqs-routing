<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\HandlerWrapper;

use DigitalCraftsman\CQRS\Test\AppTestCase;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use DigitalCraftsman\CQRS\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;

/** @coversDefaultClass \DigitalCraftsman\CQRS\HandlerWrapper\SilentExceptionWrapper */
final class SilentExceptionWrapperTest extends AppTestCase
{
    /**
     * @test
     *
     * @covers ::areParametersValid
     */
    public function are_parameters_valid_works(): void
    {
        // -- Arrange
        $silentExceptionWrapper = new SilentExceptionWrapper();

        // -- Act & Assert
        /** @psalm-suppress NullArgument */
        self::assertFalse($silentExceptionWrapper::areParametersValid(null));
        self::assertFalse($silentExceptionWrapper::areParametersValid([]));
        /** @psalm-suppress InvalidArgument */
        self::assertFalse($silentExceptionWrapper::areParametersValid([
            CreateNewsArticleCommand::class,
        ]));
        /**
         * @psalm-suppress UndefinedClass
         * @psalm-suppress ArgumentTypeCoercion
         */
        self::assertFalse($silentExceptionWrapper::areParametersValid([
            'App\InvalidException',
        ]));

        self::assertTrue($silentExceptionWrapper::areParametersValid([
            NewsArticleAlreadyExists::class,
        ]));
    }
}
