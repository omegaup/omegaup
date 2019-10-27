<?php

namespace OmegaUp;

require_once('../../server/bootstrap.php');

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

[
    'identity' => $identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();
if (
    is_null($identity) ||
    !\OmegaUp\Authorization::isSupportTeamMember($identity)
) {
    header('HTTP/1.1 404 Not found');
    die();
}

\OmegaUp\UITools::getSmartyInstance()->display(
    '../templates/admin.support.tpl'
);
