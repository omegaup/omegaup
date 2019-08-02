<?php
require_once('../server/bootstrap_smarty.php');

UITools::redirectToLoginIfNotLoggedIn();

try {
    [
        'smartyProperties' => $smartyProperties,
        'template' => $template
    ] = CourseController::getCourseDetailsForSmarty(
        new Request($_REQUEST)
    );
} catch (Exception $e) {
    Logger::getLogger('course')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('404.html'));
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display("../templates/{$template}");
