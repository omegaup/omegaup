<?php

header("X-Robots-Tag: noindex");

require_once(__DIR__ . '/../../server/bootstrap.php');

echo \OmegaUp\ApiCaller::httpEntryPoint();
