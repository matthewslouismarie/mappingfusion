<?php

namespace MF\Controller;

use MF\Router;
use PSpell\Config;
use Psr\Container\ContainerInterface;

class PageFactory
{
    public function __construct(
        private Router $router,
        private ContainerInterface $container,
    ) {
    }

    public function createPage(
        string $name,
        string $controllerFqcn,
        array $controllerParams = [],
        ?string $parentFqcn = null,
    ) {
        return new Page(
            null === $parentFqcn ? null : $this->container->get($parentFqcn)->getPage(),
            $name,
            $this->router->generateUrl($this->router->getRouteId($controllerFqcn), $controllerParams),
        );
    }
}