<?php

require_once('../server/bootstrap_smarty.php');

$smarty->assign('payload', \OmegaUp\Controllers\User::getPrivacyPolicy(new \OmegaUp\Request([])));

$smarty->display('../templates/user.privacy.policy.tpl');
