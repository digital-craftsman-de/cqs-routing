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
    /** @param int $parameters Max upload size in MB */
    public function validateDTO(
        Request $request,
        Command | Query $dto,
        mixed $parameters,
    ): void {
        $reflection = new \ReflectionClass($dto);
        foreach ($reflection->getProperties() as $prop) {
            $name = $prop->name;
            /** @psalm-suppress MixedAssignment */
            $dtoProp = $dto->$name;
            if ($dtoProp instanceof UploadedFile
                && $dtoProp->getSize() > self::megabyteToByte($parameters)
            ) {
                throw new FileSizeTooLarge(self::byteToMegabyte($dtoProp->getSize()), $parameters);
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

    /** @param int $parameters */
    public static function areParametersValid(mixed $parameters): bool
    {
        return is_int($parameters)
            && $parameters > 0;
    }
}
