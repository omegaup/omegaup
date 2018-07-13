<?php

require_once('../server/bootstrap.php');

$smarty->assign('payload', UserController::getPrivacyPolicy(new Request([])));

$smarty->display('../templates/user.privacy.policy.tpl');
