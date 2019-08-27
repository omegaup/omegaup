<?php
require_once('../server/bootstrap_smarty.php');

UITools::redirectToLoginIfNotLoggedIn();

try {
    [
        'smartyProperties' => $smartyProperties,
        'template' => $template
    ] = CourseController::getCourseDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display("../templates/{$template}");
