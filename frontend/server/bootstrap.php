<?php

/*
 * Bootstrap file
 * 
 * 
 * */

// Set default time
date_default_timezone_set('UTC');

// Loads config
define('SERVER_PATH', dirname(__DIR__));

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH);

if(!is_file(SERVER_PATH . "/config.php")){

		?>
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<HTML>
		<head>
			<link rel="stylesheet" type="text/css" href="css/style.css">
		</head>
		<body style="padding:5px">
			<h1>No config file.</h1>
				<p>You are missing the config file. It must look something like this:</p>
			<pre class="code">
				<?php include ("config.php.sample") ; ?>
			</pre>
			</body>
		</html>
		<?php

		exit;
}



require_once(SERVER_PATH . "/config.php");
require_once("libs/Logger/Logger.php");
require_once('dao/model.inc.php');
require_once("libs/Authorization.php");


/**
 * I am the API:
 * Connect to DB, and load the DAO's. 
 *
 * */
if ( defined( "WHOAMI" ) && WHOAMI == "API" ){
	
	require_once('adodb5/adodb.inc.php');
	require_once('adodb5/adodb-exceptions.inc.php');

	$conn = null;

	try{
	    $conn = ADONewConnection(OMEGAUP_DB_DRIVER);
	    $conn->debug = OMEGAUP_DB_DEBUG;
	    $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);

    	if(!$conn) {
			/**
			 * Dispatch missing parameters
			 * */
			header('HTTP/1.1 500 INTERNAL SERVER ERROR');

			die(json_encode(array(
				"status" => "error",
				"error"	 => "Conection to the database has failed.",
				"errorcode" => 1
			)));

	    }
	    $conn->SetCharSet('utf8');
	    $conn->EXECUTE('SET NAMES \'utf8\';');
	    
	} catch (Exception $e) {
		

    	    //if(!$conn) {
            	$conn = ADONewConnection(OMEGAUP_SLAVE_DB_DRIVER);
           		$conn->PConnect(OMEGAUP_SLAVE_DB_HOST, OMEGAUP_SLAVE_DB_USER, OMEGAUP_SLAVE_DB_PASS, OMEGAUP_SLAVE_DB_NAME);
        		//}
			
		/*
		header('HTTP/1.1 500 INTERNAL SERVER ERROR');
		
		die(json_encode(array(
			"status" => "error",
			"error"	 => $e,
			"errorcode" => 2
		)));
		*/
	}
	$GLOBALS["conn"] = $conn;
	return;
}



/*
 * require googleopenid lib
 * 
 * 
 * */
require_once( "libs/GoogleOpenID.php" );





/*
 * Start and evaluate session
 * 
 * 
 * 
 * */
require_once("controllers/login.controller.php");
require_once("controllers/users.controller.php");
require_once("controllers/problems.controller.php");





/*
 * This global variables, will be accessible trough all of the program
 * and will contain relevant information for the current session, and 
 * other handy global configuration options.
 * 
 *
 * */
$GLOBALS = array();	
$GLOBALS["user"] = array();
$GLOBALS["session"] = array();




/*
 * Load the rest of the base classes
 * 
 * */
require_once( "definitions.php" );
require_once( "libs/GUI/GUI.inc.php" );




/*
 * Connect to database with the appropiate permissions
 * based on the session retrived above. This way, a non-logged
 * user will have only SELECT permissions for example. If a 
 * bug is found, they will have limited access to the database.
 * 
 * */
require_once('adodb5/adodb.inc.php');
require_once('adodb5/adodb-exceptions.inc.php');

$conn = null;


try{
    $conn = ADONewConnection(OMEGAUP_DB_DRIVER);
    $conn->debug = OMEGAUP_DB_DEBUG;
    $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);

} catch (Exception $e) {
	Logger::error($e);
        //if(!$conn) {
            $conn = ADONewConnection(OMEGAUP_SLAVE_DB_DRIVER);
            $conn->PConnect(OMEGAUP_SLAVE_DB_HOST, OMEGAUP_SLAVE_DB_USER, OMEGAUP_SLAVE_DB_PASS, OMEGAUP_SLAVE_DB_NAME);
        //}
	
	//die("error");
}



/*
 * This bootstrap should be loaded via a frontend page.
 * All pages *must* set this variable to see if they have
 * permissions to see this page.
 *
 * */
/*
if( !defined(LEVEL_NEEDED)  ){
	//LEVEL_NEEDED WAS NOT SET !
	$GUI->prettyDie("LEVEL_NEEDED WAS NOT SET");
	
}else{
	//check for permissions

}
*/


function FormatTime($timestamp, $type = "FB")
{




	if(!is_numeric($timestamp)){
		$timestamp = strtotime($timestamp);
	}

	// Get time difference and setup arrays
	$difference = time() - $timestamp;
	$periods = array("segundo", "minuto", "hora", "dia", "semana", "mes", "years");
	$lengths = array("60","60","24","7","4.35","12");
 
	// Past or present
	if ($difference >= 0) 
	{
		$ending = "Hace";
	}
	else
	{
		$difference = -$difference;
		$ending = "Dentro de";
	}
 
	// Figure out difference by looping while less than array length
	// and difference is larger than lengths.
	$arr_len = count($lengths);
	for($j = 0; $j < $arr_len && $difference >= $lengths[$j]; $j++)
	{
		$difference /= $lengths[$j];
	}
 
	// Round up		
	$difference = round($difference);
 
	// Make plural if needed
	if($difference != 1) 
	{
		$periods[$j].= "s";
	}
 
	// Default format
	$text = "$ending $difference $periods[$j]";
 
	// over 24 hours
	if($j > 2)
	{
		// future date over a day formate with year
		if($ending == "to go")
		{
			if($j == 3 && $difference == 1)
			{
				$text = "Ayer a las ". date("g:i a", $timestamp);
			}
			else
			{
				$text = date("F j, Y \a \l\a\s g:i a", $timestamp);
			}
			return $text;
		}
 
		if($j == 3 && $difference == 1) // Yesterday
		{
			$text = "Ayer a las ". date("g:i a", $timestamp);
		}
		else if($j == 3) // Less than a week display -- Monday at 5:28pm
		{
			$text = date(" \a \l\a\s g:i a", $timestamp);
			
			switch(date("l", $timestamp)){
				case "Monday": 		$text = "Lunes" . $text; break;
				case "Tuesday": 	$text = "Martes" . $text; break;
				case "Wednesday": 	$text = "Miercoles" . $text; break;
				case "Thursday": 	$text = "Jueves" . $text; break;
				case "Friday": 		$text = "Viernes" . $text; break;
				case "Saturday": 	$text = "Sabado" . $text; break;
				case "Sunday": 		$text = "Domingo" . $text; break;

			}
		}
		else if($j < 6 && !($j == 5 && $difference == 12)) // Less than a year display -- June 25 at 5:23am
		{
			$text = date(" j \a \l\a\s g:i a", $timestamp);
			
			switch(date("F", $timestamp)){
				case "January": 	$text = "Enero" 	. $text; break;
				case "February": 	$text = "Febrero" 	. $text; break;
				case "March": 		$text = "Marzo" 	. $text; break;
				case "April": 		$text = "Abril" 	. $text; break;	
				case "May": 		$text = "Mayo" 	. $text; break;	
				case "June": 		$text = "Junio" 	. $text; break;	
				case "July": 		$text = "Julio" 	. $text; break;	
				case "August": 		$text = "Agosto" 	. $text; break;	
				case "September": 		$text = "Septiembre" 	. $text; break;	
				case "October": 		$text = "Octubre" 	. $text; break;	
				case "November": 		$text = "Noviembre" 	. $text; break;	
				case "December": 		$text = "Diciembre" 	. $text; break;																				
			}
		}
		else // if over a year or the same month one year ago -- June 30, 2010 at 5:34pm
		{
			$text = date(" j, Y \a \l\a\s g:i a", $timestamp);
			
			switch(date("F", $timestamp)){
				case "January": 	$text = "Enero" 	. $text; break;
				case "February": 	$text = "Febrero" 	. $text; break;
				case "March": 		$text = "Marzo" 	. $text; break;
				case "April": 		$text = "Abril" 	. $text; break;	
				case "May": 		$text = "Mayo" 	. $text; break;	
				case "June": 		$text = "Junio" 	. $text; break;	
				case "July": 		$text = "Julio" 	. $text; break;	
				case "August": 		$text = "Agosto" 	. $text; break;	
				case "September": 		$text = "Septiembre" 	. $text; break;	
				case "October": 		$text = "Octubre" 	. $text; break;	
				case "November": 		$text = "Noviembre" 	. $text; break;	
				case "December": 		$text = "Diciembre" 	. $text; break;																				
			}
		}
	}
 
 	$text = "<span title='".date("F j, Y \a \l\a\s g:i a", $timestamp)."'> " . $text . "</span>";

	return $text;
}

