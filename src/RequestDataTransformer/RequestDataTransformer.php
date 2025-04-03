<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\RequestDataTransformer;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\Query\Query;
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;

/**
 * The data transformer can have three kinds of tasks and multiple data transformers can be used with one request.
 * - Cast existing data into other formats.
 * - Sanitize existing data.
 * - Add additional data not present in the request.
 *  It must not be used to:
 * - Validate the request data in any way. That must be handled in the DTO validator.
 *
 * @see https://github.com/digital-craftsman-de/cqs-routing/blob/main/docs/process.md
 * @see https://github.com/digital-craftsman-de/cqs-routing/blob/main/docs/examples/request-data-transformer.md
 *
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
interface RequestDataTransformer
{
    /**
     * @param class-string<Command|Query>       $dtoClass
     * @param NormalizedConfigurationParameters $parameters
     */
    public function transformRequestData(
        string $dtoClass,
        array $requestData,
        mixed $parameters,
    ): array;

    /**
     * @param NormalizedConfigurationParameters $parameters
     */
    public static function areParametersValid(mixed $parameters): bool;
}
