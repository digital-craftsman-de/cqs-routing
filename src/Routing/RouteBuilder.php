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

    public static function addQueryRoute(
        RoutingConfigurator $routes,
        RouteParameters $parameters,
    ): void {
        $name = $parameters->name ?? str_replace(
            '/',
            '_',
            mb_strtolower($parameters->path),
        );
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

    public static function addCommandRoute(
        RoutingConfigurator $routes,
        RouteParameters $parameters,
    ): void {
        $name = $parameters->name ?? str_replace(
            '/',
            '_',
            mb_strtolower($parameters->path),
        );
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
}
