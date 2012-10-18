<?php

	require_once( "../server/bootstrap.php" );

// create object
$smarty = new Smarty;

// assign some content. This would typically come from
// a database or other source, but we'll use static
// values for the purpose of this example.
$smarty->assign('name', 'george smith');
$smarty->assign('address', '45th & Harris');


// display it
$smarty->display('../templates/index.tpl');