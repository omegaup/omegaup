<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 3) . '/server/bootstrap.php');

header('Content-Type: application/json');

try {
    $request = new \OmegaUp\Request($_REQUEST);
    $response = \OmegaUp\Controllers\Admin::apiUpdateSystemSettings($request);
    echo json_encode($response);
} catch (\Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
    ]);
}
