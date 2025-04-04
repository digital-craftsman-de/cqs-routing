<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\RequestValidator;

use DigitalCraftsman\CQSRouting\Routing\RoutePayload;
use Symfony\Component\HttpFoundation\Request;

/**
 * A request validator is there to validate information that is only accessible from the request itself and will not be part of the DTO or
 * must be validated before a DTO is constructed from the request data. This could include headers of a request or validation of data on an
 * application level. For example to scan uploaded files against viruses.
 *
 * Multiple request validators can be applied on each request.
 *
 *  It must not be used to:
 * - Validate request content according to business rules.
 * - Validate the existence of content that is needed for construction of command or query objects. That must be handled in the DTO constructor.
 *
 * @see https://github.com/digital-craftsman-de/cqs-routing/blob/main/docs/process.md
 * @see https://github.com/digital-craftsman-de/cqs-routing/blob/main/docs/examples/request-validator.md
 *
 * @psalm-import-type NormalizedConfigurationParameters from RoutePayload
 */
interface RequestValidator
{
    /**
     * @param NormalizedConfigurationParameters $parameters
     */
    public function validateRequest(
        Request $request,
        mixed $parameters,
    ): void;

    /**
     * @param NormalizedConfigurationParameters $parameters
     */
    public static function areParametersValid(mixed $parameters): bool;
}
