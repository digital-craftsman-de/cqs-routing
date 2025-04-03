<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\RequestValidator;

use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidator;
use Symfony\Component\HttpFoundation\Request;

final readonly class GuardAgainstTokenInHeaderRequestValidator implements RequestValidator
{
    /** @param null $parameters */
    public function validateRequest(
        Request $request,
        mixed $parameters,
    ): void {
        if ($request->headers->has('X-TOKEN')) {
            throw new \InvalidArgumentException('Token must not be supplied');
        }
    }

    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
