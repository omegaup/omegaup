<?php

require_once('../server/bootstrap_smarty.php');

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$length = isset($_GET['length']) ? $_GET['length'] : 100;
$filter = isset($_GET['filter']) ? $_GET['filter'] : null;
$to_search = isset($_GET['query']) ? $_GET['query']:null;
$Searched=null;
$r = new Request($_REQUEST);
$availableFilters = [];

$session = SessionController::apiCurrentSession($r)['session'];
if ($session['auth_token']) {
    if (!is_null($session['user']->country_id)) {
        $availableFilters['country'] = $smarty->getConfigVars('wordsFilterByCountry');
    }
    if (!is_null($session['user']->state_id)) {
        $availableFilters['state'] = $smarty->getConfigVars('wordsFilterByState');
    }
    if (!is_null($session['user']->school_id)) {
        $availableFilters['school'] = $smarty->getConfigVars('wordsFilterBySchool');
    }
}
if (strlen($to_search)>0) {
    $Searched=UsersDAO::CheckUsername($to_search);

    if (is_null($Searched)) {
        $smarty->display('../templates/searched_user.tpl');
    }
}

if (!is_null($Searched)) {
    header("location: /profile/$Searched");
}

$smarty->assign('page', $page);
$smarty->assign('length', $length);
$smarty->assign('filter', $filter);
$smarty->assign('availableFilters', $availableFilters);
$smarty->display('../templates/rank.tpl');
