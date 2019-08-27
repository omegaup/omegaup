<?php

require_once('../server/bootstrap_smarty.php');

$smarty->assign('payload', UserController::getPrivacyPolicy(new \OmegaUp\Request([])));

$smarty->display('../templates/user.privacy.policy.tpl');
