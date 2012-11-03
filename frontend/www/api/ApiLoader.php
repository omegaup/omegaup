<?php

require_once( "../../server/bootstrap.php" );



var_dump($_GET);


//get the controller
$i = strpos( $_GET["_api_"], "/" );

$s_ControllerName = substr( $_GET["_api_"], 0, $i ) . "Controller";

if( class_exists ( $s_ControllerName ) === true )
{
    $c_Controller = new $s_ControllerName;

    var_dump($c_Controller::$metadata);

    $a_Methods = get_class_methods( $c_Controller );

    echo "<h2>Api Explorer</h2>";

    for ($i=0; $i < sizeof( $a_Methods ); $i++)
    {
        echo "public " .  $a_Methods[ $i ] . " ( ); <br>";
    }



    $foo = "CurrentSesionAvailable";

    var_dump( $c_Controller->$foo( ) );
}



die();
