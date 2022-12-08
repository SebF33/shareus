<?php

use Psr\Http\Message\UploadedFileInterface;


/**
 * Convertit tous les caractères applicables en entités HTML.
 *
 * @param string|null $text Le texte à convertir
 *
 * @return string Le texte converti
 */
function html(?string $text = null): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


/**
 * Déplace le fichier uploadé vers le répertoire d'uploads et lui attribue un
 * nom unique pour éviter d'écraser un fichier uploadé existant.
 *
 * @param string $directory Le répertoire dans lequel le fichier est déplacé
 * @param string $username Le nom d'utilisateur
 * @param UploadedFileInterface $uploadedFile Le fichier uploadé à déplacer
 *
 * @return bool
 */
function moveUploadedFile(string $directory, string $username, UploadedFileInterface $uploadedFile)
{
    $basename = pathinfo($uploadedFile->getClientFilename(), PATHINFO_FILENAME);
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

    // Nouveau nom au fichier
    // http://php.net/manual/en/function.random-bytes.php
    $basename = $username . '_' . date('Y-M-d') . '_' . str_replace('_', '-', preg_replace("/\s+/", '-', $basename)) . '_' . bin2hex(random_bytes(6));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    // Placement du fichier dans le répertoire d'uploads
    $movedFile = $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $movedFile;
}


/**
 * Supprime le fichier uploadé le plus ancien.
 *
 * @param string $directory Le répertoire dans lequel se trouvent les uploads
 * @param int $number Le nombre d'uploads maximum avant suppression
 */
function deleteOlderFile(string $directory, int $number)
{
    $glob = glob($directory . DIRECTORY_SEPARATOR . "*.{jpg,jpeg,png,webp}", GLOB_BRACE);
    $imagecount = count($glob);
    if ($imagecount > $number) {
        foreach ($glob as $filepath) {
            $time = filemtime($filepath);
            $files[$time] = $filepath;
        }
        ksort($files);
        foreach ($files as $filename) {
            unlink($filename);
        }
    }
}


/**
 * Récupère le type MIME d'un fichier.
 * 
 * @param string $ext L'extension du fichier
 */
function getMimeType(string $ext)
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

        // Images
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

        // Archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // Audio/vidéo
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // Adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // MS Office
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc' => 'application/msword',
        'dot' => 'application/msword',
        'dotx' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',

        // OpenOffice
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    }
}
