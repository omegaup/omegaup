<?php
    require_once('../../server/bootstrap_smarty.php');
if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

    $smarty->display('../templates/hackathon.tpl');
