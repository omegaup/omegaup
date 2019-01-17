<?php
    require_once('../server/bootstrap_smarty.php');

    $smarty->assign('LOAD_MATHJAX', true);

    $smarty->display('../templates/redaccion.tpl');
