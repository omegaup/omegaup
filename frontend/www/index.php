<?php

	require_once( "../server/bootstrap.php" );

// create object
$smarty = new Smarty;


$smarty->setTemplateDir( SERVER_PATH . '\\..\\templates\\' );
$smarty->setCacheDir( "C:\\Users\\Alan\\Desktop\\cache" )->setCompileDir(  "C:\\Users\\Alan\\Desktop\\cache" );
$smarty->configLoad("C:\\xampp\\htdocs\\omegaup\\omegaup\\frontend\\templates\\es.lang");
//$smarty->configLoad("C:\\xampp\\htdocs\\omegaup\\omegaup\\frontend\\templates\\en.lang");

$smarty->display( '../templates/login.tpl' );