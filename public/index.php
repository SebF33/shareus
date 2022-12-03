<?php

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;


require __DIR__ . '/../vendor/autoload.php';


// Dossier d'uploads
$uploadDir = '/uploads';
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();
$container->set('upload_directory', __DIR__ . $uploadDir);
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
      // Poids maxi : 1Mo => 1024*1024
      if ($uploadedFile->getError() === UPLOAD_ERR_INI_SIZE && $uploadedFile->getError() === UPLOAD_ERR_FORM_SIZE) :
        echo 'Fichier trop volumineux (1Mo max).';
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
          // Poids maxi : 1Mo => 1024*1024
          if ($uploadedFile->getSize() > 1048576) {
            echo 'Fichier trop volumineux (1Mo max).';
            return $response->withStatus(400);
          }
          // Re-vérification du type du fichier côté serveur
          // .jpg, .png et .webp
          elseif (
            $uploadedFile->getClientMediaType() != 'image/jpeg'
            and $uploadedFile->getClientMediaType() != 'image/png'
            and $uploadedFile->getClientMediaType() != 'image/webp'
          ) {
            echo 'Le fichier n\'est pas une image valide.';
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

  $galleryDir = 'uploads/';

  $renderer = new PhpRenderer(__DIR__ . '/../templates/');
  return $renderer->render($response, 'swiper.html.php', ["galleryDir" => $galleryDir]);
})->setName('swiper');


// Download personnalisé
$app->get('/download/{filename}', function ($request, $response, $args) {

  $directory = $this->get('upload_directory');
  $file = $directory . DIRECTORY_SEPARATOR . $args['filename'];
  $extension = pathinfo($file, PATHINFO_EXTENSION);
  $mimetype = getMimeType($extension);

  $fh = fopen($file, 'rb');
  $stream = new \Slim\Psr7\Stream($fh);

  return $response->withHeader('Content-Type', 'application/force-download')
    ->withHeader('Content-Type', $mimetype)
    ->withHeader('Content-Type', 'application/download')
    ->withHeader('Content-Description', 'File Transfer')
    ->withHeader('Content-Transfer-Encoding', 'binary')
    ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
    ->withHeader('Expires', '0')
    ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
    ->withHeader('Pragma', 'public')
    ->withHeader('Content-Length', filesize($file))
    ->withBody($stream);
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


function getMimeType($ext)
{
  $mime_types = array(

    'txt' => 'text/plain',
    'htm' => 'text/html',
    'html' => 'text/html',
    'php' => 'text/html',
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'xml' => 'application/xml',
    'swf' => 'application/x-shockwave-flash',
    'flv' => 'video/x-flv',

    // images
    'png' => 'image/png',
    'jpe' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'bmp' => 'image/bmp',
    'ico' => 'image/vnd.microsoft.icon',
    'tiff' => 'image/tiff',
    'tif' => 'image/tiff',
    'svg' => 'image/svg+xml',
    'svgz' => 'image/svg+xml',
    'webp' => 'image/webp',

    // archives
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    'exe' => 'application/x-msdownload',
    'msi' => 'application/x-msdownload',
    'cab' => 'application/vnd.ms-cab-compressed',

    // audio/video
    'mp3' => 'audio/mpeg',
    'qt' => 'video/quicktime',
    'mov' => 'video/quicktime',

    // adobe
    'pdf' => 'application/pdf',
    'psd' => 'image/vnd.adobe.photoshop',
    'ai' => 'application/postscript',
    'eps' => 'application/postscript',
    'ps' => 'application/postscript',

    // ms office
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/msword',
    'dot' => 'application/msword',
    'dotx' => 'application/msword',
    'rtf' => 'application/rtf',
    'xls' => 'application/vnd.ms-excel',
    'ppt' => 'application/vnd.ms-powerpoint',

    // open office
    'odt' => 'application/vnd.oasis.opendocument.text',
    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
  );

  if (array_key_exists($ext, $mime_types)) {
    return $mime_types[$ext];
  }
}


// Lancer l'application
$app->run();
