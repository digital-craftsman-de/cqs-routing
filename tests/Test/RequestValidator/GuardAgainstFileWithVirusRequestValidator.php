<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\RequestValidator;

use DigitalCraftsman\CQSRouting\RequestValidator\RequestValidator;
use DigitalCraftsman\CQSRouting\Test\Utility\VirusScannerSimulator;
use Symfony\Component\HttpFoundation\Request;

final readonly class GuardAgainstFileWithVirusRequestValidator implements RequestValidator
{
    public function __construct(
        private VirusScannerSimulator $virusScanner,
    ) {
    }

    /** @param null $parameters */
    #[\Override]
    public function validateRequest(
        Request $request,
        mixed $parameters,
    ): void {
        foreach ($request->files as $file) {
            $this->virusScanner->scanForVirus($file);
        }
    }

    /** @param null $parameters */
    #[\Override]
    public static function areParametersValid(mixed $parameters): bool
    {
        return $parameters === null;
    }
}
