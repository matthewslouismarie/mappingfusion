<?php

namespace MF;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Router
{
    public function generateUrl(string $routeId, array $parameters = []): string {
        $url = "/?route_id=$routeId";
        foreach ($parameters as $key => $value) {
            $url .= "&$key=$value";
        }
        return $url;
    }

    public function generateRedirect(string $routeId, $parameters = []): ResponseInterface {
        return new Response(302, ['Location' => $this->generateUrl($routeId, $parameters)]);
    }
}