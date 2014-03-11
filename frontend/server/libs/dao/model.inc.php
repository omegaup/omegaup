<?php

require_once ('Estructura.php');

spl_autoload_register(function ($class) {
	if (substr($class, -3) == "DAO")
	{
		$class = substr($class, 0, -3);
	}
	$file_name = (preg_replace('/([a-z])([A-Z])/', '$1_$2', $class));
	@include 'libs/dao/' . $file_name . '.dao.php';
});
