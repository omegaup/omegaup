<?php
require_once('../server/bootstrap.php');
if (!$experiments->isEnabled(Experiments::SCHOOLS)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

if (isset($_REQUEST['course'])) {
    $data = CourseController::apiClone(new Request(['course_alias' => $_REQUEST['course']]));
    header('Location: /course/' . $data['alias'] . '/edit/#edit');
}
