<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\RequestValidator;

use Symfony\Component\HttpFoundation\Request;

interface RequestValidatorInterface
{
    public function validateRequest(Request $request): void;
}
