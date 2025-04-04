<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test\Router;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final readonly class RouterFake implements RouterInterface
{
    public function __construct(
        private RouteCollection $routeCollection,
    ) {
    }

    #[\Override]
    public function setContext(RequestContext $context): void
    {
    }

    #[\Override]
    public function getContext(): RequestContext
    {
        return new RequestContext();
    }

    #[\Override]
    public function getRouteCollection(): RouteCollection
    {
        return $this->routeCollection;
    }

    #[\Override]
    public function generate(
        string $name,
        array $parameters = [],
        int $referenceType = self::ABSOLUTE_PATH,
    ): string {
        return '';
    }

    #[\Override]
    public function match(string $pathinfo): array
    {
        return [];
    }
}
