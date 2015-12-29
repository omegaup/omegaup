<?php

define('WHOAMI', 'API');
require_once('../../server/inc/bootstrap.php');

// Scumbag IE y su cache agresivo.
header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

require_once('../../server/api/ApiLoader.php');
echo ApiLoader::load();
