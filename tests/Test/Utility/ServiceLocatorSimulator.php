<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Test\Utility;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Contracts\Service\ServiceProviderInterface;

final class ServiceLocatorSimulator implements ServiceProviderInterface
{
    /** @param array<string, object> $providedServices */
    public function __construct(
        /** @var array<string, object> */
        private array $providedServices,
    ) {
    }

    public function get(string $id): mixed
    {
        return $this->providedServices[$id] ?? throw new ServiceNotFoundException($id);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->providedServices);
    }

    public function getProvidedServices(): array
    {
        return array_keys($this->providedServices);
    }
}
