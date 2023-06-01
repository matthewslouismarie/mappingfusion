<?php

namespace MF;

use GuzzleHttp\Psr7\Response;
use MF\Controller\AccountController;
use MF\Controller\ArticleController;
use MF\Controller\AuthController;
use MF\Controller\HomeController;
use Psr\Http\Message\ServerRequestInterface;

// @todo Move http bridge code away from the rest of the code, and call it in index.php?
class Kernel
{
    private AccountController $account;

    private ArticleController $article;

    private HomeController $home;

    private AuthController $registration;

    public function __construct(
        ArticleController $article,
        AccountController $account,
        HomeController $home,
        AuthController $registration,
    ) {
        $this->account = $account;
        $this->article = $article;
        $this->home = $home;
        $this->registration = $registration;
    }

    public function manageRequest(ServerRequestInterface $request): void {
        if (!isset($request->getQueryParams()['route_id'])) {
            $this->processResponse($this->home->handleHomePage($request));
        } else {
            switch ($request->getQueryParams()['route_id']) {
                case 'login':
                    $this->processResponse($this->registration->handleLoginPage($request));
                    break;
            
                case 'register':
                    $this->processResponse($this->registration->handleRegistrationPage($request));
                    break;
                
                case 'account':
                    $this->processResponse($this->account->handleAccountPage($request));
                    break;

                case 'article':
                    $this->processResponse($this->article->handleArticlePage($request));
                    break;
                
                default:
                    echo 'page not found';
            }
        }
    }

    public function processResponse(Response $response): void {
        if (302 === $response->getStatusCode()) {
            header('Location: ' . $response->getHeaderLine('Location'));
            die();
        } else {
            echo $response->getBody()->__toString(); 
        }
    }
}