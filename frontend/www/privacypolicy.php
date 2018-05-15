<?php

require_once('../server/bootstrap.php');

$privacy_policy = UserController::getPrivacyPolicy(new Request([]));

$smarty->assign('PRIVACY_POLICY', $privacy_policy);

$smarty->display('../templates/user.privacy.policy.tpl');
