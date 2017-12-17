<?php

    require_once('../server/bootstrap.php');

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $filter = isset($_GET['filter']) ? $_GET['filter'] : null;

    $r = new Request($_REQUEST);
    $filters = [];
    $session = SessionController::apiCurrentSession($r)['session'];
if ($session['auth_token']) {
    if (!is_null($session['user']->country_id)) {
        $filters['country'] = 'country';
    }
    if (!is_null($session['user']->state_id)) {
        $filters['state'] = 'state';
    }
    if (!is_null($session['user']->school_id)) {
        $filters['school'] = 'school';
    }
}

    $smarty->assign('page', $page);
    $smarty->assign('filter', $filter);
    $smarty->assign('filters', $filters);

    $smarty->display('../templates/rank.tpl');
