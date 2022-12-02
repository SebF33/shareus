<?php

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;


require __DIR__ . '/../vendor/autoload.php';


// Dossier d'uploads
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();
$container->set('upload_directory', __DIR__ . '/uploads');
AppFactory::setContainer($container);


// Créer l'application
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});


// Page d'accueil
$app->get('/', function ($request, $response, $args) {
  $renderer = new PhpRenderer(__DIR__ . '/../templates/');
  return $renderer->render($response, 'home.html.php', $args);
})->setName('accueil');


// Upload personnalisé
$app->post('/', function (ServerRequestInterface $request, ResponseInterface $response) {

  $directory = $this->get('upload_directory');

  // Validation du nom d'utilisateur
  $username = "";
  if (empty(trim($_POST["username"]))) {
    echo 'Merci d\'entrer votre nom d\'utilisateur puis de renouveler votre envoi.';
    return $response->withStatus(400);
  } elseif (strlen(trim($_POST["username"])) > 10) {
    echo 'Votre nom ne doit pas contenir plus de 10 caractères.';
    return $response->withStatus(400);
  } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
    echo 'Votre nom peut uniquement contenir des lettres, chiffres et underscores.';
    return $response->withStatus(400);
  } else {

    // Nettoyage du nom d'utilisateur
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');

    // Upload multiple
    $uploadedFiles = $request->getUploadedFiles();
    foreach ($uploadedFiles['file'] as $uploadedFile) {
      // Gestion des erreurs de la super globale $_FILES
      // https://www.php.net/manual/en/features.file-upload.errors.php
      // Erreur 1 : La taille du fichier téléchargé excède la valeur de upload_max_filesize, configurée dans le php.ini
      // Erreur 2 : La taille du fichier téléchargé excède la valeur de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire
      if ($uploadedFile->getError() === UPLOAD_ERR_INI_SIZE && $uploadedFile->getError() === UPLOAD_ERR_FORM_SIZE) :
        echo 'Fichier trop volumineux (1Mo max).'; // Poids maxi : 1Mo => 1024*1024
        return $response->withStatus(400);
      // Erreur 3 : Le fichier n'a été que partiellement téléversé
      // Erreur 6 : Un dossier temporaire est manquant
      // Erreur 7 : Échec de l'écriture du fichier sur le disque
      // Erreur 8 : Une extension PHP a arrêté l'envoi de fichier
      elseif ($uploadedFile->getError() === UPLOAD_ERR_PARTIAL || $uploadedFile->getError() === UPLOAD_ERR_NO_TMP_DIR || $uploadedFile->getError() === UPLOAD_ERR_CANT_WRITE || $uploadedFile->getError() === UPLOAD_ERR_EXTENSION) :
        echo 'Un problème est survenu pendant le téléversement.';
        return $response->withStatus(500);
      else :
        // Erreur 4 : Aucun fichier n'a été téléversé
        if ($uploadedFile->getError() === UPLOAD_ERR_NO_FILE) :
          echo 'Aucun fichier n\'a été téléversé.';
          return $response->withStatus(400);
        else :
          // Re-vérification de la taille du fichier côté serveur
          if ($uploadedFile->getSize() > 1048576) {
            echo 'Fichier trop volumineux (1Mo max).'; // Poids maxi : 1Mo => 1024*1024
            return $response->withStatus(400);
          }
          // Récupération et stockage du fichier sur le serveur
          else {

            $movedFile = moveUploadedFile($directory, $username, $uploadedFile);

            if ($movedFile === false) {
              // Échec du transfert
              echo 'Un problème est survenu, merci de renouveler votre envoi.';
              return $response->withStatus(500);
            } else {
              // Succès
              echo 'Le fichier a été envoyé avec succès.';
            }
          }

        endif;
      endif;
    }
    return $response->withStatus(200);
  }
});


// Swiper
$app->get('/swiper', function ($request, $response, $args) {
  $renderer = new PhpRenderer(__DIR__ . '/../templates/');
  return $renderer->render($response, 'swiper.html.php', $args);
})->setName('swiper');


// Download personnalisé
$app->get('/download/{filename}', function ($request, $response, $args) {

  $file = '/uploads/' . $args['filename'];

  return $response->withHeader('Content-Type', 'application/force-download')
    ->withHeader('Content-Type', FILEINFO_MIME_TYPE)
    ->withHeader('Content-Type', 'application/download')
    ->withHeader('Content-Description', 'File Transfer')
    ->withHeader('Content-Transfer-Encoding', 'binary')
    ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
    ->withHeader('Expires', '0')
    ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
    ->withHeader('Pragma', 'public')
    ->withBody((new \Slim\Psr7\Stream(fopen($file, 'rb'))));
});


/**
 * Déplace le fichier uploadé vers le répertoire d'uploads et lui attribue un
 * nom unique pour éviter d'écraser un fichier uploadé existant.
 *
 * @param string $directory Le répertoire dans lequel le fichier est déplacé
 * @param string $username Le nom d'utilisateur
 * @param UploadedFileInterface $uploadedFile Le fichier uploadé à déplacer
 *
 * @return string Le nom de fichier du fichier déplacé
 */
function moveUploadedFile(string $directory, string $username, UploadedFileInterface $uploadedFile)
{
  $basename = pathinfo($uploadedFile->getClientFilename(), PATHINFO_FILENAME);
  $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

  // Nouveau nom au fichier
  // http://php.net/manual/en/function.random-bytes.php
  $basename = $username . '_' . date('Y-M-d') . '_' . str_replace('_', '-', preg_replace("/\s+/", "", $basename)) . '_' . bin2hex(random_bytes(6));
  $filename = sprintf('%s.%0.8s', $basename, $extension);

  // Placement du fichier dans le répertoire d'uploads
  $movedFile = $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

  return $movedFile;
}


// Lancer l'application
$app->run();
