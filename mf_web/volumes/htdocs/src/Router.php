<?php

namespace MF;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Router
{
    public function __construct(
        private Configuration $config,
        private TwigService $twig,
    ) {
    }

    public function generateUrl(string $routeId, array $parameters = [], string $paramStart = '?', string $hash = ''): string {
        $url = "/$routeId";
        if ('' !== $hash) {
            $url .= "#$hash";
        }
        if (0 !== count($parameters)) {
            foreach ($parameters as $param) {
                $url .= "/$param";
            }
        }
        return $this->config->getSetting('homeUrl') . $url;
    }

    public function generateRedirect(string $routeId, $parameters = []): ResponseInterface {
        return new Response(302, ['Location' => $this->generateUrl($routeId, $parameters)]);
    }

    public function createResponse(
        string $templatePath,
        array $params,
    ) {
        return new Response(
            status: 200,
            body: $this->twig->render($templatePath, $params),
        );
    }
}