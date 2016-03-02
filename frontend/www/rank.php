<?php

    require_once('../server/bootstrap.php');

    $page = isset($_GET['page']) ? $_GET['page'] : 1;

    $smarty->assign('page', $page);

    $smarty->display('../templates/rank.tpl');
