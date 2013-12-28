<?php

	require_once( "../../server/bootstrap.php" );

	UITools::redirectToLoginIfNotLoggedIn();
	UITools::redirectIfNoAdmin();

	$lines = Logger::read(1500);

	$html = "<pre style='overflow: hidden;  width: 100%; background: whiteSmoke; margin-bottom:5px; font-size:9.5px;'>";

	for ($a = sizeof($lines) - 1; $a >= 0 ; $a-- ) {
		$linea = explode(  "|", $lines[$a] );
		if( sizeof($linea) > 1 ) {
			$ip = $linea[1];
			$octetos = explode(".", $ip);
			if (sizeof($octetos) == 4) {
				$html .= "<div style='color: white; background-color: rgb( " . $octetos[1] . " , " . $octetos[2] . " , " . $octetos[3] . ")'>" . $lines[$a] . "\n</div>";
			}else{
				$html .= "<div style='color: white; background-color: rgb(  0,0,0)'>" . $lines[$a] . "\n</div>" ;
			}
		}else{
			$html .= "<div>" . $lines[$a] . "\n</div>" ;
		}
	}

	$html .= "</pre>";

	$smarty->assign('logContents', $html);

	$smarty->display( '../../templates/admin.log.tpl' );
