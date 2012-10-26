<?php

/**
  * Description:
  *     Sesion controller handles sesions.
  *
  * Author:
  *     Alan Gonzalez alanboy@alanboy.net
  *
  **/
class SesionController extends Controller
{
    private static $current_sesion;




    private function isAuthTokenValid( $s_AuthToken )
    {
        //do some other basic testing on s_AuthToken

        return true;
    }


    public function CurrentSesionAvailable( )
    {
        $a_CurrentSesion = $this->CurrentSesion( );

        return $a_CurrentSesion[ "valid" ] ;
    }

    /**
      * Returns associative array with information about current sesion.
      *
      **/
    public function CurrentSesion( )
    {

        $SesionM = $this->getSessionManagerInstance( );

        $s_AuthToken = $SesionM->GetCookie( OMEGAUP_AUTH_TOKEN_COOKIE_NAME );

        $vo_CurrentUser = NULL;

        //cookie contains an auth token
        if( !is_null( $s_AuthToken ) && $this->isAuthTokenValid( $s_AuthToken ) )
        {
            $vo_CurrentUser = AuthTokensDAO::getUserByToken( $s_AuthToken );
        }
        else if ( isset( $_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] ) && $this->isAuthTokenValid( $s_AuthToken = $_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] ) )
        {
            $vo_CurrentUser = AuthTokensDAO::getUserByToken( $_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] );
        }
        else
        {
            return array(
                    'valid' => false,
                    'id' => NULL,
                    'name' => NULL,
                    'username' => NULL,
                    'email' => NULL,
                    'auth_token' => NULL
                );
        }

        //get email via his id
        $s_MainEmail = "alkadjflkajd@laksfjald.net";

        return array(
                'valid' => true,
                'id' => $vo_CurrentUser->getUserId( ),
                'name' => $vo_CurrentUser->getName( ),
                'email' => $s_MainEmail,
                'username' => NULL,
                'auth_token' => $s_AuthToken
            );

    }


    /**
      * 
      *
      **/
    public function UnRegisterSesion( )
    {
        $a_CurrentSesion = $this->CurrentSesion( );

        $vo_AuthT = new AuthTokens( array( "token" => $a_CurrentSesion["auth_token"] ) );

        try{
            AuthTokensDAO::delete( $vo_AuthT );


        }catch(Exception $e){
            
        }

        setcookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, 'deleted', 1, '/');
    }



    private function RegisterSesion( Users $vo_User, $b_ReturnAuthTokenAsString = false)
    {
        //find if this user has older sessions
         $vo_AuthT = new AuthTokens( );
         $vo_AuthT->setUserId( $vo_User->getUserId( ) );

        //erase them

        $s_AuthT = time( ) . "-" . $vo_User->getUserId() . "-" . md5( OMEGAUP_MD5_SALT . $vo_User->getUserId( ) . time( ) );


        $vo_AuthT = new AuthTokens();
        $vo_AuthT->setUserId( $vo_User->getUserId( ) );
        $vo_AuthT->setToken( $s_AuthT );

        try
        {
            AuthTokensDAO::save( $vo_AuthT );
        }
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation( ), $e);
        }

        if ( $b_ReturnAuthTokenAsString )
        {
            return $s_AuthT;
        }
        else
        {
            $sm = $this->getSessionManagerInstance( );

            $sm->SetCookie( OMEGAUP_AUTH_TOKEN_COOKIE_NAME, $s_AuthT, time( )+60*60*24, '/' );
        }
    }





    public function LoginViaGoogle( $s_Email )
    {
        //we trust this user's identity

        $c_Users = new UserController;

        $vo_User = $c_Users->FindByEmail( $s_Email );

        if ( is_null( $vo_User ) )
        {
            //user has never logged in before
            Logger::log( "LoginViaGoogle: Creating new user for $s_Email" );
        }
        else
        {
            //user has been here before, lets just register his sesion
            $this->RegisterSesion( $vo_User );
        }

    }


    /**
      *
      *
      *
      *
      *
      **/
    public function NativeLogin( $s_UsernameOrEmail = null, $s_Password = null, $b_ReturnAuthToken = false )
    {
        $c_Users = new UserController( );

        $vo_User = null;

        if ( ( $vo_User = $c_Users->FindByEmail( $s_UsernameOrEmail ) )
                || ( $vo_User = $c_Users->FindByUsername( $s_UsernameOrEmail ) ) )
        {
            //found user
        }
        else
        {
            return false;
        }

        //assert c_users valid

        $b_Valid = $c_Users->TestPassword( $vo_User->getUserId( ), null, null, $s_Password );

        if ( !$b_Valid )
        {
            return false;
        }

        try
        {
            return $this->RegisterSesion( $vo_User, $b_ReturnAuthToken );
        }
        catch( Exception $e )
        {

        }

    }

}