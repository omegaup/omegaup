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
$smarty->setTemplateDir( SERVER_PATH . '\\..\\templates\\' );
$smarty->setCacheDir( "C:\\Users\\Alan\\Desktop\\cache" );

$smarty->display( '../templates/login.tpl' );