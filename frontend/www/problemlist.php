<?php

function getTagList() {
    if (!isset($_GET['tag'])) {
        return null;
    }
    $tags = $_GET['tag'];
    // Still allow strings to be sent to avoid breaking permalinks.
    if ($tags === '') {
        $tags = [];
    }
    if (!is_array($tags)) {
        $tags = explode(',', (string)$tags);
    }
    return array_unique($tags);
}

require_once('../server/bootstrap.php');
$r = new Request();
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'asc';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'title';
$language = isset($_GET['language']) ? $_GET['language'] : null;
$tags = getTagList();

$r['page'] = $page;
$r['language'] = $language;
$r['order_by'] = $order_by;
$r['mode'] = $mode;
$r['tag'] = $tags;

$keyword = '';
if (!empty($_GET['query']) && strlen($_GET['query']) > 0) {
    $keyword = substr($_GET['query'], 0, 256);
    $r['query'] = $keyword;
}
$response = ProblemController::apiList($r);

$params = ['query' => $keyword, 'language' => $language, 'order_by' => $order_by, 'mode' => $mode, 'tag' => $tags];

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
$smarty->assign('LANGUAGE', $language);
$smarty->assign('problems', $response['results']);
$smarty->assign('current_tags', $tags);
$smarty->assign('pager_items', $pager_items);
$smarty->display('../templates/problems.tpl');
