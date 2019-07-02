<?php

require_once('../server/bootstrap_smarty.php');
if (isset($_REQUEST['badge'])) {
  $badgeAlias = $_REQUEST['badge'];
  $response = BadgeController::apiList(new Request([]));
  if (in_array($badgeAlias, $response)) {
    $smarty->assign('badge', $_REQUEST['badge']);
  } else {
    $smarty->assign('STATUS_ERROR', '{error}');
  }
  // Assign it
  // Display it
  $smarty->display('../templates/badge.details.tpl');
}