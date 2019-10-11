<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

try {
    /** @var array{smartyProperties: array{coursePayload?: array{name: string, description: string, alias: string, currentUsername: string, needsBasicInformation: bool, requestsUserInformation: string, shouldShowAcceptTeacher: bool, statements: array{privacy: array{markdown: string|null, gitObjectId: null|string, statementType: null|string}, acceptTeacher: array{gitObjectId: string|null, markdown: string, statementType: string}}, isFirstTimeAccess: bool, shouldShowResults: bool}, showRanking?: bool, payload?: array{shouldShowFirstAssociatedIdentityRunWarning: bool}}, template: string} */
    [
        'smartyProperties' => $smartyProperties,
        'template' => $template
    ] = \OmegaUp\Controllers\Course::getCourseDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(sprintf('%s/templates/%s', OMEGAUP_ROOT, $template));
