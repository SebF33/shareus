<?php

use DI\ContainerBuilder;
use Slim\App;
use Slim\Csrf\Guard;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();


$containerBuilder = new ContainerBuilder();

// Ajoute les définitions du conteneur DI
$containerBuilder->addDefinitions(__DIR__ . '/container.php');

// Crée une instance du conteneur DI
$container = $containerBuilder->build();

// Dossier d'uploads
$uploadDir = '/../public/uploads';
$container->set('upload_directory', __DIR__ . $uploadDir);

// Crée une instance de l'application Slim
$app = $container->get(App::class);

// Protection CSRF
$responseFactory = $app->getResponseFactory();
$container->set('csrf', function () use ($responseFactory) {
  return new Guard($responseFactory);
});
$app->add('csrf');

// Enregistre les routes
(require __DIR__ . '/routes.php')($app);

// Enregistre le middleware
(require __DIR__ . '/middleware.php')($app);


return $app;
