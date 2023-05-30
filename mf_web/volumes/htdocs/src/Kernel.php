<?php

namespace MF;

use MF\Controller\RegistrationController;
use Twig\Environment;

class Kernel
{
    private Environment $twig;
    private RegistrationController $registration;
    private Request $request;

    public function __construct(
        TwigService $twigService,
        RegistrationController $registration,
        RequestFactory $requestFactory,
    ) {
        $this->request = $requestFactory->createRequest();
        $this->twig = $twigService->getTwig();
        $this->registration = $registration;
    }

    public function manageRequest() {
        switch ($this->request->getQueryParams()['route_id']) {
            case 'login':
                echo $this->twig->load('login.html.twig')->render();
                break;
        
            case 'register':
                echo $this->registration->processRequest($this->request);
                break;
            
            case null:
                echo $this->twig->load('index.html.twig')->render();
                break;
            
            default:
                echo 'page not found';
        }
    }
}