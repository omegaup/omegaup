<?php

require_once('../server/bootstrap_smarty.php');

UITools::setProfile($smarty);

$smarty->display('../templates/user.profile.tpl');
