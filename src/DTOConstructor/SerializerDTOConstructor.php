<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\DTOConstructor;

use DigitalCraftsman\CQSRouting\Command\Command;
use DigitalCraftsman\CQSRouting\Query\Query;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class SerializerDTOConstructor implements DTOConstructorInterface
{
    /** @codeCoverageIgnore */
    public function __construct(
        private DenormalizerInterface $serializer,
    ) {
    }

    /**
     * @psalm-template T of Command|Query
     *
     * @psalm-param class-string<T> $dtoClass
     *
     * @psalm-return T
     */
    public function constructDTO(array $requestData, string $dtoClass): Command | Query
    {
        /** @psalm-var T */
        return $this->serializer->denormalize($requestData, $dtoClass);
    }
}
