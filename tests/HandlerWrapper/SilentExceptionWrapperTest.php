<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\HandlerWrapper;

use DigitalCraftsman\CQSRouting\Test\AppTestCase;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\CreateNewsArticleCommand;
use DigitalCraftsman\CQSRouting\Test\Domain\News\WriteSide\CreateNewsArticle\Exception\NewsArticleAlreadyExists;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\Exception\TaskAlreadyAccepted;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\MarkTaskAsAccepted\MarkTaskAsAcceptedCommand;
use DigitalCraftsman\CQSRouting\Test\ValueObject\TaskId;
use Symfony\Component\HttpFoundation\Request;

/** @coversDefaultClass \DigitalCraftsman\CQSRouting\HandlerWrapper\SilentExceptionWrapper */
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

    /**
     * @test
     *
     * @covers ::catch
     */
    public function catch_works(): void
    {
        // -- Arrange
        $silentExceptionWrapper = new SilentExceptionWrapper();

        // -- Act & Assert
        // An exception other than the thrown one is caught, means it should be returned.
        self::assertEquals(
            new TaskAlreadyAccepted(),
            $silentExceptionWrapper->catch(
                dto: new MarkTaskAsAcceptedCommand(
                    taskId: TaskId::generateRandom(),
                ),
                request: new Request(),
                parameters: [
                    // Exception that is not thrown
                    NewsArticleAlreadyExists::class,
                ],
                exception: new TaskAlreadyAccepted(),
            ),
        );

        // The exception should be swallowed when it's thrown.
        self::assertNull(
            $silentExceptionWrapper->catch(
                dto: new MarkTaskAsAcceptedCommand(
                    taskId: TaskId::generateRandom(),
                ),
                request: new Request(),
                parameters: [
                    TaskAlreadyAccepted::class,
                ],
                exception: new TaskAlreadyAccepted(),
            ),
        );
    }
}
