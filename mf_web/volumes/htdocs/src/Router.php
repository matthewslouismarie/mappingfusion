<?php

namespace MF;

class Router
{
    public function generateUrl(string $routeId, array $parameters = []): string {
        $url = "/?route_id=$routeId";
        foreach ($parameters as $key => $value) {
            $url .= "&$key=$value";
        }
        return $url;
    }
}