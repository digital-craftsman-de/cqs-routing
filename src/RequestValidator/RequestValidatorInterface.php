<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\RequestValidator;

use Symfony\Component\HttpFoundation\Request;

/**
 * Request validation is there to validate information that is only accessible from the request itself and will not be part of the DTO or
 * must be validated before a DTO is constructed from the request data.
 *
 * Multiple request validators can be applied on each request.
 *
 * @see https://github.com/digital-craftsman-de/cqrs/blob/main/docs/request-validator.md
 */
interface RequestValidatorInterface
{
    public function validateRequest(Request $request): void;
}
