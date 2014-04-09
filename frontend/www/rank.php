<?php

    require_once( "../server/bootstrap.php" );

	$page = isset($_GET["p"]) ? $_GET["p"] : 0;

	$smarty->assign('page', $page );

    $smarty->display( '../templates/rank.tpl' );
