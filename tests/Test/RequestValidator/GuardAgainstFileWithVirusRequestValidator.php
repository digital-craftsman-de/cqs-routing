<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\RequestValidator;

use DigitalCraftsman\CQRS\RequestValidator\RequestValidatorInterface;
use DigitalCraftsman\CQRS\Test\Utility\VirusScannerSimulator;
use Symfony\Component\HttpFoundation\Request;

final readonly class GuardAgainstFileWithVirusRequestValidator implements RequestValidatorInterface
{
    public function __construct(
        private VirusScannerSimulator $virusScanner,
    ) {
    }

    /** @param null $parameters */
    public function validateRequest(
        Request $request,
        mixed $parameters,
    ): void {
        foreach ($request->files as $file) {
            $this->virusScanner->scanForVirus($file);
        }
    }

    /** @param null $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
