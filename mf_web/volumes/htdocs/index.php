<?php

require_once './vendor/autoload.php';

use MF\RequestFactory;
use MF\Controller\RegistrationController;

$env = parse_ini_file('.env');

$pdo = new PDO("mysql:host=mf_db", 'root', $env['DB_ROOT_PWD'], [PDO::ATTR_PERSISTENT => true]);
$stmt = $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . $env['DB_NAME']);
$stmt = $pdo->exec('USE ' . $env['DB_NAME']);

$request = (new RequestFactory())->createRequest();

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'cache' => 'cache',
]);

switch ($request->getQueryParams()['route_id']) {
    case 'login':
        echo $twig->load('login.html.twig')->render();
        break;

    case 'register':
        $controller = new RegistrationController($twig);
        echo $controller->processRequest($request);
        break;
    
    case null:
        echo $twig->load('index.html.twig')->render();
        break;
    
    default:
        echo 'page not found';
}
