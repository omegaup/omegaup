<?php

require_once('../server/bootstrap.php');

UITools::setProfile($smarty);

$smarty->display('../templates/profile.tpl');
