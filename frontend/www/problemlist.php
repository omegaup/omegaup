<?php
    require_once( "../server/bootstrap.php" );

	$r = new Request();
	$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
	$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'problem_id';

	$r['page'] = $page;
	$r['order_by'] = $order_by;
	$response = ProblemController::apiList($r);

	$total = intval($response['total']);
	$total_pages = intval(ceil($total / PROBLEMS_PER_PAGE) + 1E-9);
	if ($page < 1 || $page > $total_pages) {
		$page = 1;
	}

	$adjacent = 5;
	$pager_links = array();
	$prev = array('label' => 'Previous', 'url' => '', 'class' => '');
	if ($page > $adjacent + 1) {
		$prev['url'] = '/problem/list?page=' . ($page - 1) . '&order_by=' . $order_by;
	} else {
		$prev['url'] = '';
		$prev['class'] = 'disabled';
	}
	array_push($pager_links, $prev);

	if ($page > $adjacent + 1) {
		$first = array(
			'label' => '1',
			'url'   => '/problem/list?page=1&order_by=' . $order_by,
			'class' => ''
		);
		$period = array(
			'label' => '...',
			'url'	=> '',
			'class' => 'disabled'
		);
		array_push($pager_links, $first);
		array_push($pager_links, $period);
	}

	for ($i = max(1, $page - $adjacent); $i < $page; $i++) {
		$item = array(
			'label' => $i,
			'url'   => '/problem/list?page=' . $i . '&order_by=' . $order_by,
			'class' => ''

		);
		array_push($pager_links, $item);
	}

	$current = array(
		'label' => $page,
		'url'   => '',
		'class' => 'active'
	);
	array_push($pager_links, $current);

	for ($i = $page + 1; $i <= min($total_pages, $page + $adjacent); $i++) {
		$item = array(
			'label' => $i,
			'url'   => '/problem/list?page=' . $i . '&order_by=' . $order_by,
			'class' => ''
		);
		array_push($pager_links, $item);
	}

	if ($page + $adjacent < $total_pages) {
		$period = array(
			'label' => '...',
			'url'	=> '',
			'class' => 'disabled'
		);
		$last = array(
			'label' => $total_pages,
			'url'   => '/problem/list?page=' . $total_pages . '&order_by=' . $order_by,
			'class' => ''
		);
		array_push($pager_links, $period);
		array_push($pager_links, $last);
	}

	$next = array('label' => 'Next', 'url' => '', 'class' => '');
	if ($page + $adjacent < $total_pages) {
		$next['url'] = '/problem/list?page=' . ($page + 1) . '&order_by=' . $order_by;
	} else {
		$next['url'] = '';
		$next['class'] = 'disabled';
	}
	array_push($pager_links, $next);

	$smarty->assign('problems', $response['results']);
	$smarty->assign('pager_links', $pager_links);
    $smarty->display( '../templates/problems.tpl' );
