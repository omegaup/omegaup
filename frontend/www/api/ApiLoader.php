<?php

require_once( "../../server/bootstrap.php" );



$b_isExplorer = $_GET["_explorer_"];
$s_ApiAsUrl = $_GET["_api_"];

$a_Args = explode( "/", $s_ApiAsUrl );
$c_Controller = NULL;
$s_ControllerName = ucfirst( $a_Args[ 0 ] ) . "Controller";




// Just to double check that we are
// only instatiate a controller.
switch( $s_ControllerName )
{
    case "SesionController":
    case "UserController":
        $c_Controller = new $s_ControllerName;
        $smarty->assign( 'CONTROLLER_NAME', ucfirst( $a_Args[ 0 ] ) );
    break;
}


// No controller given
if ( is_null( $c_Controller ) )
{
    if ( $b_isExplorer )
    {
        $smarty->assign( 'msg', 'API_NO_CONTROLLER' );
        $smarty->display( '../templates/api.tpl' );
        exit;

    }else{
        //show404();
        die("404");
    }
}



// No method
if( sizeof( $a_Args ) == 1 )
{
    // no method given
    if ( $b_isExplorer )
    {
        
        $a_Methods = get_class_methods( $c_Controller );
        $smarty->assing( "METHODS", array( $a_Methods ) );

        $smarty->assign( "msg", "API_NO_METHOD" );
        $smarty->display( "../templates/api.tpl" );
        exit;
    }
    else
    {
        //show404();
        die("404");

    }
}


$s_Method = $a_Args[ 1 ];



// Empty method
if( strlen( $s_Method ) == 0 )
{
    // no method given
    if ( $b_isExplorer )
    {
        $r_Controller = new ReflectionClass( $c_Controller );

        $a_Methods = array();

        $ar_Methods = $r_Controller->getMethods( );

        foreach ($ar_Methods as $method )
        {
            $s_Params = "";

            foreach( $method->getParameters( ) as $param )
            {

                if( $param ->isOptional( ) )
                {
                    $s_Params .= $param ->getName( );
                }else{
                    $s_Params .= "<b>" . $param ->getName( ) . "</b>";
                }

                $s_Params .= "/";
            }

            array_push( $a_Methods, array( 
                    "name" => $method->getName( ),
                    "params" => $s_Params
                ) );
        }

        $smarty->assign( "METHODS", $a_Methods );

        $smarty->assign( 'msg', 'API_NO_METHOD' );

        $smarty->display( '../templates/api.tpl' );
        exit;
    }
    else
    {
        //show404();
        die("404");

    }
}




try
{
    $r_Method = new ReflectionMethod( $s_ControllerName, $s_Method );
}
catch( ReflectionException $refEx )
{
    if ( $b_isExplorer )
    {
        $smarty->assign( 'msg', 'API_NOT_A_METHOD' );
        $smarty->display( '../templates/api.tpl' );
        exit;
    }
    else
    {
        //show404();
        die("404");
    }
}



if( ! $r_Method->isPublic( ) )
{
    if ( $b_isExplorer )
    {
        $smarty->assign( 'msg', 'API_NOT_A_METHOD' );
        $smarty->display( '../templates/api.tpl' );
        exit;
    }
    else
    {
        //show404();
        die("404");
    }
}



if( $r_Method->getNumberOfRequiredParameters( ) > ( sizeof( $a_Args ) - 2 ) )
{
    if ( $b_isExplorer )
    {
        $smarty->assign( 'msg', 'API_MISSING_PARAMS' );
        $smarty->display( '../templates/api.tpl' );
        exit;
    }
    else
    {
        //show404();
        die("404");
    }
}

//shift controller name and methods name
array_shift( $a_Args );
array_shift( $a_Args );


try{

    $result = call_user_func_array( array($c_Controller, $s_Method ), $a_Args );

    if( $b_isExplorer )
    {
        $smarty->assign( 'msg', 'API_EXECTUTED' );
        $smarty->display( '../templates/api.tpl' );
    }
    else
    {
        $result["status"] = "ok";
        echo json_encode( $result );
    }

}catch( ApiException $apiException ){

    Logger::ApiException( $apiException );

    if( $b_isExplorer )
    {
        $smarty->assign( 'msg', 'API_FAILED_GRACEFULY' );
        $smarty->display( '../templates/api.tpl' );
    }
    else
    {
        echo json_encode( array( "status" => "error", "reason" => $apiException->getApiMessage( ) ) );
    }

}catch( Exception $e ){

    Logger::Exception( $e );

    if( $b_isExplorer )
    {
        $smarty->assign( 'msg', 'API_ERROR' );
        $smarty->display( '../templates/api.tpl' );
    }
    else
    {
        echo json_encode( array( "status" => "error", "reason" => "GENERIC_ERROR_REASON" ) );
    }
}




