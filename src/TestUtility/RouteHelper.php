<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\TestUtility;

use DigitalCraftsman\CQSRouting\Controller\QueryController;
use DigitalCraftsman\CQSRouting\Routing\RouteBuilder;
use DigitalCraftsman\CQSRouting\Routing\RouteConfiguration;
use DigitalCraftsman\CQSRouting\Routing\RouteConfigurationBuilder;
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

final readonly class RouteHelper
{
    public function __construct(
        private RouterInterface $router,
        private RouteConfigurationBuilder $routeConfigurationBuilder,
    ) {
    }

    public function getRouteConfiguration(string $name): RouteConfiguration
    {
        $route = $this->router->getRouteCollection()->get($name);
        if ($route === null) {
            throw new Exception\RouteNotFound($name);
        }

        if (!self::isCQSRoute($route)) {
            throw new Exception\RouteIsNoNotACQSRoute($name);
        }

        /**
         * We know that it's set as we checked right before.
         *
         * @var array $routePayloadData
         */
        $routePayloadData = $route->getDefault(RouteBuilder::ROUTE_PAYLOAD_KEY);

        $controller = $route->getDefault('_controller');

        return $controller === QueryController::class
            ? $this->routeConfigurationBuilder->buildConfigurationForQuery(
                RoutePayload::fromPayload($routePayloadData),
            )
            : $this->routeConfigurationBuilder->buildConfigurationForCommand(
                RoutePayload::fromPayload($routePayloadData),
            );
    }

    public static function isCQSRoute(Route $route): bool
    {
        return array_key_exists(
            key: RouteBuilder::ROUTE_PAYLOAD_KEY,
            array: $route->getDefaults(),
        );
    }
}
