<?php

	require_once( "../server/bootstrap.php" );

	$contes_alias = $_REQUEST["alias"];

	$smarty->display( '../templates/contest.details.tpl' );
