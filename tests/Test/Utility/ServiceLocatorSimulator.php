<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Utility;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @template-covariant T of mixed
 *
 * @implements ServiceProviderInterface<T>
 */
final readonly class ServiceLocatorSimulator implements ServiceProviderInterface
{
    /** @param array<string, object> $providedServices */
    public function __construct(
        /** @var array<string, object> $providedServices */
        private array $providedServices,
    ) {
    }

    #[\Override]
    public function get(string $id): object
    {
        return $this->providedServices[$id] ?? throw new ServiceNotFoundException($id);
    }

    #[\Override]
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->providedServices);
    }

    /** @return array<string, string> */
    #[\Override]
    public function getProvidedServices(): array
    {
        $services = [];
        foreach ($this->providedServices as $identifier => $service) {
            $services[$identifier] = $service::class;
        }

        return $services;
    }
}
