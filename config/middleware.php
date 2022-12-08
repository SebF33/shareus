<?php

use Slim\App;


return function (App $app) {

    // Parse le JSON, les données de formulaire et le XML
    $app->addBodyParsingMiddleware();

    // Ajoute le middleware de routage intégré de Slim
    $app->addRoutingMiddleware();

    // Gère les exceptions
    $app->addErrorMiddleware(true, true, true);
};
