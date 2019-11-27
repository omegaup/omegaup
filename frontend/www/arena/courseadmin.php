<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

\OmegaUp\UITools::renderWithEmptyResponse('arena.course.admin.tpl');
