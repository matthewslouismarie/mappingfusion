<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use MF\Controller\ControllerInterface;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class AdminTestController implements ControllerInterface
{
    public function __construct(private TwigService $twig) {
    }

    public function generateResponse(ServerRequestInterface $request, array $params): Response {
        return new Response(body: $this->twig->render('admin_test.html.twig'));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}