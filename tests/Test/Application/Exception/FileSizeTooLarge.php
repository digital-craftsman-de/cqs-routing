<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Application\Exception;

/** @psalm-immutable */
final class FileSizeTooLarge extends \DomainException
{
    public function __construct(int $actualSize, int $maximumSize)
    {
        parent::__construct(sprintf(
            'The file size is too large (%dMB). The maximum allowed file size is %dMB.',
            $actualSize,
            $maximumSize,
        ));
    }
}
