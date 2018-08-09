<?php
    require_once('../server/bootstrap.php');

    $smarty->assign('LOAD_MATHJAX', true);

    $smarty->display('../templates/redaccion.tpl');
