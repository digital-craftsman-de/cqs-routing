<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQRS\Routing;

use DigitalCraftsman\CQRS\Controller\CommandController;
use DigitalCraftsman\CQRS\Controller\QueryController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RouteConfigurator;

final class RouteBuilder
{
    private const DEFAULT_METHOD = Request::METHOD_POST;

    public static function addQueryRoute(
        RouteConfigurator $routes,
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
                'routePayload' => RoutePayload::fromRouteParameters($parameters),
            ]);
    }

    public static function addCommandRoute(
        RouteConfigurator $routes,
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
                'routePayload' => RoutePayload::fromRouteParameters($parameters),
            ]);
    }
}
