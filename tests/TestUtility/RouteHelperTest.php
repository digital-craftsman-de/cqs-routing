<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\TestUtility;

use DigitalCraftsman\CQSRouting\DTOConstructor\SerializerDTOConstructor;
use DigitalCraftsman\CQSRouting\RequestDecoder\JsonRequestDecoder;
use DigitalCraftsman\CQSRouting\ResponseConstructor\EmptyResponseConstructor;
use DigitalCraftsman\CQSRouting\Routing\RouteBuilder;
use DigitalCraftsman\CQSRouting\Routing\RouteConfigurationBuilder;
use DigitalCraftsman\CQSRouting\Routing\RoutePayload;
use DigitalCraftsman\CQSRouting\Test\AppTestCase;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommand;
use DigitalCraftsman\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask\CreateTaskCommandHandler;
use DigitalCraftsman\CQSRouting\Test\Router\RouterFake;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(RouteHelper::class)]
#[CoversClass(Exception\RouteNotFound::class)]
#[CoversClass(Exception\RouteIsNoNotACQSRoute::class)]
final class RouteHelperTest extends AppTestCase
{
    #[Test]
    public function get_route_configuration_works(): void
    {
        // -- Arrange
        $routeCollection = new RouteCollection();
        $routeCollection->add(
            'test_route',
            new Route(
                path: '/api/test-route',
                defaults: [
                    '_controller' => 'TestController',
                    RouteBuilder::ROUTE_PAYLOAD_KEY => RoutePayload::generatePayload(
                        dtoClass: CreateTaskCommand::class,
                        handlerClass: CreateTaskCommandHandler::class,
                        requestDecoderClass: JsonRequestDecoder::class,
                        dtoConstructorClass: SerializerDTOConstructor::class,
                        responseConstructorClass: EmptyResponseConstructor::class,
                    ),
                ],
            ),
        );

        $router = new RouterFake($routeCollection);

        $routeHelper = new RouteHelper(
            router: $router,
            routeConfigurationBuilder: $this->getContainerService(RouteConfigurationBuilder::class),
        );

        // -- Act
        $routeConfiguration = $routeHelper->getRouteConfiguration('test_route');

        // -- Assert
        self::assertSame(CreateTaskCommand::class, $routeConfiguration->dtoClass);
    }

    #[Test]
    public function get_route_configuration_fails_without_route(): void
    {
        // -- Assert
        $this->expectException(Exception\RouteNotFound::class);

        // -- Arrange
        $routeCollection = new RouteCollection();

        $router = new RouterFake($routeCollection);

        $routeHelper = new RouteHelper(
            router: $router,
            routeConfigurationBuilder: $this->getContainerService(RouteConfigurationBuilder::class),
        );

        // -- Act
        $routeHelper->getRouteConfiguration('test_route');
    }

    #[Test]
    public function get_route_configuration_fails_with_route_that_is_not_a_cqs_route(): void
    {
        // -- Assert
        $this->expectException(Exception\RouteIsNoNotACQSRoute::class);

        // -- Arrange
        $routeCollection = new RouteCollection();
        $routeCollection->add(
            'test_route',
            new Route(
                path: '/api/test-route',
                defaults: [
                    '_controller' => 'TestController',
                ],
            ),
        );

        $router = new RouterFake($routeCollection);

        $routeHelper = new RouteHelper(
            router: $router,
            routeConfigurationBuilder: $this->getContainerService(RouteConfigurationBuilder::class),
        );

        // -- Act
        $routeHelper->getRouteConfiguration('test_route');
    }
}
