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

    // Create our Application instance (replace this with your appId and secret).
    private static $_facebook;

    /**
      * @param string nombre Este es el nombre del dude
      *
      **/
    private static function getFacebookInstance( )
    {
        
        if(is_null(self::$_facebook)){
            self::$_facebook = new Facebook(array(
                'appId'  => OMEGAUP_FB_APPID,
                'secret' => OMEGAUP_FB_SECRET
            ));
        } 

        return self::$_facebook;
    }


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

        $s_AuthToken = $SesionM->getCookie( OMEGAUP_AUTH_TOKEN_COOKIE_NAME );

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
        $vo_Email = EmailsDAO::getByPK( $vo_CurrentUser->getMainEmailId( ) );

        return array(
                'valid' => true,
                'id' => $vo_CurrentUser->getUserId( ),
                'name' => $vo_CurrentUser->getName( ),
                'email' => $vo_Email->getEmail( ),
                'username' => $vo_CurrentUser->getUsername( ),
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

            $sm->setCookie( OMEGAUP_AUTH_TOKEN_COOKIE_NAME, $s_AuthT, time( )+60*60*24, '/' );
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




    public function LoginViaFacebook( $s_Email, $s_FacebookId )
    {

        //ok, the user does not have any auth token
        //if he wants to test facebook login
        //Facebook must send me the state=something
        //query, so i dont have to be testing 
        //facebook sesions on every single petition
        //made from the front-end
        if(!isset($_GET["state"])){
            Logger::log("Not logged in and no need to check for fb session");
            return false;
        }
        
        
        Logger::log("There is no auth_token cookie, testing for facebook sesion.");

        
        //if that is not true, may still be logged with
        //facebook, lets test that
        $facebook = self::getFacebookInstance();
        
        // Get User ID
        $fb_user = $facebook->getUser();


        // We may or may not have this data based on whether the user is logged in.
        //
        // If we have a $fb_user id here, it means we know the user is logged into
        // Facebook, but we don't know if the access token is valid. An access
        // token is invalid if the user logged out of Facebook.
        /*var_dump($fb_user);*/
        
        if ($fb_user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $fb_user_profile = $facebook->api('/me');
                
            } catch (FacebookApiException $e) {
                $fb_user = null;
                Logger::error("FacebookException:" . $e);
            }
        }
        /*var_dump($fb_user);*/
        
        // Now we know if the user is authenticated via facebook
        if (is_null($fb_user)) {
            Logger::log("No facebook sesion... ");
            return false;
        }


        //ok we know the user is logged in,
        //lets look for his information on the database
        //if there is none, it means that its the first
        //time the user has been here, lets register his info
        Logger::log("User is logged in via facebook !!");

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