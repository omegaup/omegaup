<?php

require_once('../server/bootstrap.php');

UITools::setProfile($smarty);

// Fetch contests
try {
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../templates/profile.tpl');
