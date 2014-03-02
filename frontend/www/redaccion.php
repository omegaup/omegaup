<?php
	require_once('../server/bootstrap.php');
	require_once('../server/libs/Markdown/markdown.php');

	$smarty->assign('LOAD_MATHJAX', true);

	$smarty->display( '../templates/redaccion.tpl' );
