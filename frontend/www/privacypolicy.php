<?php

require_once('../server/bootstrap.php');

$privacy_policy = UserController::getPrivacyPolicy(new Request([]));

$smarty->assign('payload', $privacy_policy);

$smarty->display('../templates/user.privacy.policy.tpl');
