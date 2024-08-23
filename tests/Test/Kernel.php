<?php

declare(strict_types=1);

namespace DigitalCraftsman\CQSRouting\Test;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('./config/{packages}/*.yaml');
        $container->import('./config/{packages}/'.$this->getEnvironment().'/*.yaml');

        $container->import('./config/{services}_test.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('./config/{routes}/'.$this->getEnvironment().'/*.yaml');
        $routes->import('./config/{routes}/*.yaml');
        $routes->import('./config/{routes}/*.php');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('./config/routes.yaml');
        } else {
            $routes->import('./config/{routes}.php');
        }
    }

    /**
     * Gets the path to the configuration directory.
     */
    private function getConfigDir(): string
    {
        return $this->getProjectDir().'/tests/Test/config';
    }
}
