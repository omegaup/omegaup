<?php

require_once('../server/bootstrap.php');

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$filter = isset($_GET['filter']) ? $_GET['filter'] : null;

$r = new Request($_REQUEST);
$availableFilters = [];
$session = SessionController::apiCurrentSession($r)['session'];
if ($session['auth_token']) {
    if (!is_null($session['user']->country_id)) {
        $availableFilters['country'] = 'country';
    }
    if (!is_null($session['user']->state_id)) {
        $availableFilters['state'] = 'state';
    }
    if (!is_null($session['user']->school_id)) {
        $availableFilters['school'] = 'school';
    }
}

$smarty->assign('page', $page);
$smarty->assign('filter', $filter);
$smarty->assign('availableFilters', $availableFilters);

$smarty->display('../templates/rank.tpl');
