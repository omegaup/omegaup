<?php

	/*
	 * LEVEL_NEEDED defines the users who can see this page.
	 * Anyone without permission to see this page, will	
	 * be redirected to a page saying so.
	 * This variable *must* be set in order to bootstrap
	 * to continue. This is by design, in order to prevent
	 * leaving open holes for new pages.
	 * 
	 * */
	define( "LEVEL_NEEDED", true );

	require_once( "../../server/inc/bootstrap.php" );


    $page = new OmegaupAdminComponentPage();
    $page->addComponent( new TitleComponent("Log"));
	$page->addComponent( new TitleComponent("Last 100 lines", 3));
	

	$lines =  Logger::read(1500);

	$html = "<pre style='overflow: hidden;  width: 100%; background: whiteSmoke; margin-bottom:5px; font-size:9.5px;'>";

	for($a = sizeof($lines) - 1; $a >= 0 ; $a-- ){
	    $linea = explode(  "|", $lines[$a] );

		if( sizeof($linea) > 1 ){
			$ip = $linea[1];
			$octetos = explode(".", $ip);
			if(sizeof($octetos) == 4){
				$html .= "<div style='color: white; background-color: rgb( " . $octetos[1] . " , " . $octetos[2] . " , " . $octetos[3] . ")'>" . $lines[$a] . "\n</div>" ;				
			}else{
				$html .= "<div style='color: white; background-color: rgb(  0,0,0)'>" . $lines[$a] . "\n</div>" ;				
			}


		}else{

			$html .= "<div>" . $lines[$a] . "\n</div>" ;		
		}
	}
	
	$html .= "</pre>";
	
	$page->addComponent( new FreeHtmlComponent( $html ) );

	
	
    $page->render();