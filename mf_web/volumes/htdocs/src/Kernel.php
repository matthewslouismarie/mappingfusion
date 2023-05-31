<?php

namespace MF;

use MF\Controller\AccountController;
use MF\Controller\ArticleController;
use MF\Controller\AuthController;
use Twig\Environment;

class Kernel
{
    private AccountController $account;

    private ArticleController $article;

    private Environment $twig;

    private AuthController $registration;

    private Request $request;

    public function __construct(
        ArticleController $article,
        AccountController $account,
        TwigService $twigService,
        AuthController $registration,
        RequestFactory $requestFactory,
    ) {
        $this->account = $account;
        $this->article = $article;
        $this->request = $requestFactory->createRequest();
        $this->twig = $twigService->getTwig();
        $this->registration = $registration;
    }

    public function manageRequest() {
        switch ($this->request->getQueryParams()['route_id']) {
            case 'login':
                echo $this->registration->handleLoginPage($this->request);
                break;
        
            case 'register':
                echo $this->registration->handleRegistrationPage($this->request);
                break;
            
            case 'account':
                echo $this->account->handleAccountPage($this->request);
                break;

            case 'article':
                echo $this->article->handleArticlePage($this->request);
                break;

            case null:
                echo $this->twig->load('index.html.twig')->render();
                break;
            
            default:
                echo 'page not found';
        }
    }
}