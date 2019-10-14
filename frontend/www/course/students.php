<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    $result = \OmegaUp\Controllers\Course::getStudentsInformationForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(sprintf('%s/templates/course.students.tpl', OMEGAUP_ROOT));
