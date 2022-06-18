<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\DTOValidator\DTOValidatorInterface;
use DigitalCraftsman\CQRS\Query\Query;
use DigitalCraftsman\CQRS\Test\Application\Exception\FileSizeTooLarge;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class FileSizeValidator implements DTOValidatorInterface
{
    public function __construct(
        private int $maxUploadSizeInMB,
    ) {
    }

    public function validateDTO(
        Request $request,
        Command|Query $dto,
    ): void {
        $reflection = new \ReflectionClass($dto);
        foreach ($reflection->getProperties() as $prop) {
            $name = (string) $prop->name;
            /** @psalm-suppress MixedAssignment */
            $dtoProp = $dto->$name;
            if ($dtoProp instanceof UploadedFile) {
                if ($dtoProp->getSize() > self::megabyteToByte($this->maxUploadSizeInMB)) {
                    throw new FileSizeTooLarge(self::byteToMegabyte($dtoProp->getSize()), $this->maxUploadSizeInMB);
                }
            }
        }
    }

    private static function megabyteToByte(int $mb): int
    {
        return $mb * 1024 * 1024;
    }

    public static function byteToMegabyte(int $bytes): int
    {
        return (int) round($bytes / 1024 / 1024);
    }
}
