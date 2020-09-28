<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

try {
    \OmegaUp\Controllers\Problem::apiInput(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (\OmegaUp\Exceptions\ExitException $e) {
    exit;
} catch (\Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}
