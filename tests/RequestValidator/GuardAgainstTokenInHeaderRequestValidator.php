<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\RequestValidator;

use Symfony\Component\HttpFoundation\Request;

final class GuardAgainstTokenInHeaderRequestValidator implements RequestValidatorInterface
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
}
