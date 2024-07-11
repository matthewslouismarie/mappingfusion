<?php

namespace MF\Controller;

use Closure;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\Router;
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
        array $parentControllerParams = [],
        bool $isIndexed = true,
        bool $partOfHierarchy = true,
    ) {
        return new Page(
            null === $parentFqcn ? null : $this->container->get($parentFqcn)->getPage($parentControllerParams),
            $name,
            $this->router->generateUrl($this->router->getRouteId($controllerFqcn), $controllerParams),
            $isIndexed,
            $partOfHierarchy,
        );
    }

    public function create(
        string $name,
        string $controllerFqcn,
        array $controllerParams = [],
        ?string $parentFqcn = null,
        ?Closure $getParent = null,
        bool $isIndexed = true,
        bool $partOfHierarchy = true,
    ) {
        
        return $this->createFromUri(
            $name,
            $this->router->generateUrl($this->router->getRouteId($controllerFqcn), $controllerParams),
            $parentFqcn,
            $getParent,
            $isIndexed,
            $partOfHierarchy,
        );
    }

    public function createFromUri(
        string $name,
        string $uri,
        ?string $parentFqcn = null,
        ?Closure $getParent = null,
        bool $isIndexed = true,
        bool $partOfHierarchy = true,
    ) {
        $parentPage = null;
        if (null !== $parentFqcn) {
            $parentController = $this->container->get($parentFqcn);
            if (null === $getParent) {
                $getParent = function (SinglePageOwner $pageGenerator) {
                    return $pageGenerator->getPage();
                };
            }
            $parentPage = $getParent($parentController);
        }
        return new Page(
            $parentPage,
            $name,
            $uri,
            $isIndexed,
            $partOfHierarchy,
        );
    }
}