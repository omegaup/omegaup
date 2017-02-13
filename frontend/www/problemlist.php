<?php

require_once('../server/bootstrap.php');
$r = new Request();
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'asc';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'title';

$r['page'] = $page;
$r['order_by'] = $order_by;
$r['mode'] = $mode;
if (!empty($_GET['tag'])) {
    $r['tag'] = $_GET['tag'];
}
$keyword = '';
if (!empty($_GET['query']) && strlen($_GET['query']) > 0) {
    $keyword = substr($_GET['query'], 0, 256);
    $r['query'] = $keyword;
}
$response = ProblemController::apiList($r);

$params = ['query' => $keyword, 'order_by' => $order_by, 'mode' => $mode];
if (!empty($_GET['tag'])) {
    $params['tag'] = $_GET['tag'];
}
$pager_items = Pager::paginate(
    $response['total'],
    $page,
    '/problem/list/',
    5,
    $params
);

$smarty->assign('KEYWORD', $keyword);
$smarty->assign('MODE', $mode);
$smarty->assign('ORDER_BY', $order_by);
$smarty->assign('problems', $response['results']);
$smarty->assign('pager_items', $pager_items);
$smarty->display('../templates/problems.tpl');
