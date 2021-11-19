<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\DTOConstructor;

use DigitalCraftsman\CQRS\Command\Command;
use DigitalCraftsman\CQRS\Query\Query;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SerializerDTOConstructor implements DTOConstructorInterface
{
    public function __construct(
        private DenormalizerInterface $serializer,
    ) {
    }

    /**
     * @return Command|Query
     *
     * @psalm-template T of Command|Query
     * @psalm-param class-string<T> $dtoClass
     * @psalm-return T
     */
    public function constructDTO(array $dtoData, string $dtoClass): object
    {
        /** @psalm-var T */
        return $this->serializer->denormalize($dtoData, $dtoClass);
    }
}
