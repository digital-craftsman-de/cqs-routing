<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing;

use DigitalCraftsman\CQRS\Controller\CommandController;
use DigitalCraftsman\CQRS\Controller\QueryController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @codeCoverageIgnore
 * There seems to be no way to get a RoutingConfigurator instance. Therefore, it's not really possible to test this builder.
 */
final class RouteBuilder
{
    private const DEFAULT_METHOD = Request::METHOD_POST;

    /**
     * Helper method to reduce noise in routing.
     * Default name is generated from path. Set it specifically when you're using the name as a reference somewhere.
     * Default method is POST.
     */
    public static function addQueryRoute(
        RoutingConfigurator $routes,
        RouteParameters $parameters,
    ): void {
        $name = $parameters->name ?? self::generateNameFromPath($parameters->path);
        $methods = [$parameters->method ?? self::DEFAULT_METHOD];

        $routes->add(
            $name,
            $parameters->path,
        )
            ->controller([QueryController::class, 'handle'])
            ->methods($methods)
            ->defaults([
                'routePayload' => RoutePayload::generatePayloadFromRouteParameters($parameters),
            ]);
    }

    /**
     * Helper method to reduce noise in routing.
     * Default name is generated from path. Set it specifically when you're using the name as a reference somewhere.
     * Default method is POST.
     */
    public static function addCommandRoute(
        RoutingConfigurator $routes,
        RouteParameters $parameters,
    ): void {
        $name = $parameters->name ?? self::generateNameFromPath($parameters->path);
        $methods = [$parameters->method ?? self::DEFAULT_METHOD];

        $routes->add(
            $name,
            $parameters->path,
        )
            ->controller([CommandController::class, 'handle'])
            ->methods($methods)
            ->defaults([
                'routePayload' => RoutePayload::generatePayloadFromRouteParameters($parameters),
            ]);
    }

    public static function generateNameFromPath(string $path): string
    {
        $path = str_starts_with($path, '/')
            ? substr($path, 1)
            : $path;

        return str_replace(
            ['/', '-', '{', '}'],
            ['_', '_', '', ''],
            mb_strtolower($path),
        );
    }
}
