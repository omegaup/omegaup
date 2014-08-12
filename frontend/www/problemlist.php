<?php
    require_once( "../server/bootstrap.php" );
	$r = new Request();
	$mode = isset($_GET['mode']) ? $_GET['mode'] : 'asc';
	$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
	$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'title';

	$r['page'] = $page;
	$r['order_by'] = $order_by;
	$r['mode'] = $mode;
	$response = ProblemController::apiList($r);

	$pager_links = Pager::paginate(
		'/problem/list',
		$page,
		5,
		$response['total'],
		array('order_by' => $order_by, 'mode' => $mode)
	);

	$smarty->assign('MODE', $mode);
	$smarty->assign('ORDER_BY', $order_by);
	$smarty->assign('problems', $response['results']);
	$smarty->assign('pager_links', $pager_links);
    $smarty->display( '../templates/problems.tpl' );
