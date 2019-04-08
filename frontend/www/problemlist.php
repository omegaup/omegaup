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

function getDifficultyRange() {
    $min_val = isset($_GET['min_difficulty']) ? intval($_GET['min_difficulty']) : -1;
    $max_val = isset($_GET['max_difficulty']) ? intval($_GET['max_difficulty']) : -1;
    if ($min_val <= $max_val && $min_val > -1 && $max_val > -1 && $min_val < 5 && $max_val < 5) {
        return array($min_val, $max_val);
    }
    return null;
}

require_once('../server/bootstrap_smarty.php');
$r = new Request();
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'asc';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'title';
$language = isset($_GET['language']) ? $_GET['language'] : null;
$tags = getTagList();
$require_all_tags = isset($_GET['some_tags']) ? false : null;
$only_karel = isset($_GET['only_karel']) ? ['kp', 'kj'] : null;
$difficulty_range = getDifficultyRange();

$r['page'] = $page;
$r['language'] = $language;
$r['order_by'] = $order_by;
$r['mode'] = $mode;
$r['tag'] = $tags;
$r['require_all_tags'] = $require_all_tags;
$r['programming_languages'] = $only_karel;
$r['difficulty_range'] = $difficulty_range;

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

foreach ($response['results'] as $key => $problem) {
    $response['results'][$key]['difficulty'] = $response['results'][$key]['difficulty'] ? floatval($problem['difficulty']) : null;
    $response['results'][$key]['quality'] = $response['results'][$key]['quality'] ? floatval($problem['quality']) : null;
    $response['results'][$key]['points'] = floatval($problem['points']);
    $response['results'][$key]['ratio'] = floatval($problem['ratio']);
    $response['results'][$key]['score'] = floatval($problem['score']);
}

$smarty->assign('KEYWORD', $keyword);
$smarty->assign('MODE', $mode);
$smarty->assign('ORDER_BY', $order_by);
$smarty->assign('LANGUAGE', $language);
$smarty->assign('problems', $response['results']);
$smarty->assign('current_tags', $tags);
$smarty->assign('pager_items', $pager_items);
$smarty->display('../templates/problems.tpl');
