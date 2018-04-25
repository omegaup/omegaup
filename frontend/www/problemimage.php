<?php

require_once('../server/bootstrap.php');

if (empty($_REQUEST['alias']) || preg_match('/[a-zA-Z0-9-_]+$/', $_REQUEST['alias']) !== 1) {
    header('HTTP/1.1 404 Not Found');
    die();
}

if (empty($_REQUEST['filename']) || preg_match('/^[^\/]+$/', $_REQUEST['filename']) !== 1) {
    header('HTTP/1.1 404 Not Found');
    die();
}

try {
    $artifacts = new ProblemArtifacts($_REQUEST['alias']);
    $mime_type = 'image/png';
    $extension = pathinfo($_REQUEST['filename'], PATHINFO_EXTENSION);
    switch ($extension) {
        case 'jpg':
        case 'jpe':
        case 'jpeg':
            $mime_type = 'image/jpeg';
            break;
        case 'png':
            $mime_type = 'image/png';
            break;
        case 'gif':
            $mime_type = 'image/gif';
            break;
        case 'bmp':
            $mime_type = 'image/bmp';
            break;
        case 'ico':
            $mime_type = 'image/vnd.microsoft.icon';
            break;
        case 'tiff':
        case 'tif':
            $mime_type = 'image/tiff';
            break;
        case 'svg':
        case 'svgz':
            $mime_type = 'image/svg+xml';
            break;
    }
    header("Content-Type: ${mime_type}");
    die($artifacts->get("statements/${_REQUEST['filename']}"));
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die();
}
