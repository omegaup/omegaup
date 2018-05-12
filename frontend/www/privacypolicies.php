<?php

require_once('../server/bootstrap.php');

$privacy_policies = UserController::getPrivacyPolicies(new Request([]));

$smarty->assign('PRIVACY_POLICIES', $privacy_policies);

$smarty->display('../templates/user.privacy.policies.tpl');
