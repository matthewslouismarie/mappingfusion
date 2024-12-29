<?php

namespace MF;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Configuration;
use LM\WebFramework\Http\HttpRequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    public function __construct(
        private Configuration $config,
        private HttpRequestHandler $httpRequestHandler,
    ) {
    }

    public function getRequestUrl(ServerRequestInterface $request): string {
        $uri = $request->getUri();
        return "{$uri->getScheme()}://{$uri->getHost()}{$uri->getPath()}";
    }

    public function getRouteId(string $controllerfqcn): ?string {
        foreach ($this->config->getRoutes() as $routeId => $fqcn) {
            if ($controllerfqcn === $fqcn) {
                return $routeId;
            }
        }
        return null;
    }

    /**
     * Get the URL from the controller class name (without the namespace), and
     * the controller parameters.
     */
    public function getUrl(string $controllerClassName, array $parameters = []): string
    {
        $routeId = $this->getRouteId("MF\\Controller\\{$controllerClassName}");
        return $this->generateUrl($routeId, $parameters);
    }

    public function generateUrl(string $routeId = '', array $parameters = [], string $paramStart = '?', string $hash = ''): string {
        $url = "/$routeId";
        if (0 !== count($parameters)) {
            foreach ($parameters as $param) {
                $url .= "/$param";
            }
        }
        if ('' !== $hash) {
            $url .= "#$hash";
        }
        return $this->config->getHomeUrl() . $url;
    }

    /**
     * @todo To delete / rename?
     */
    public function generateRedirect(string $routeId, $parameters = []): ResponseInterface
    {
        return new Response(302, ['Location' => $this->generateUrl($routeId, $parameters)]);
    }

    /**
     * @return string[]
     */
    public function getRouteParams(ServerRequestInterface $request): array
    {
        return $this->httpRequestHandler->extractRouteParams($request);
    }

    public function redirect(string $controllerFqcn, $parameters = []): ResponseInterface
    {
        return $this->generateRedirect(
            $this->getRouteId($controllerFqcn),
            $parameters,
        );
    }
}