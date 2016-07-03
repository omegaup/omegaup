<?php

require_once('../../server/bootstrap.php');

UITools::setProfile($smarty);

$smarty->display('../../templates/interviews.results.tpl');
