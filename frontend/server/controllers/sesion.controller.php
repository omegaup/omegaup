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


    /**
      * Returns associative array with information about current sesion.
      *
      **/
    public function GetCurrentSesion( )
    {
        return array(
            "valid_session" => 1,
            "user_id" => 1
            );
    }


    /**
      * 
      *
      **/



        private function UnRegisterSesion( AuthTokens $auth_token )
    {

        try
        {
            AuthTokensDAO::delete( $auth_token );

        }
        catch(Exception $e)
        {
            

        }

    }



    private function RegisterSesion( Users $vo_User, $b_ReturnAuthTokenAsString )
    {
        //find if this user has older sessions
         $vo_AuthT = new AuthTokens( );
         $vo_AuthT->setUserId( $vo_User->getUserId( ) );

        //erase them

        $s_AuthT = time( ) . "-" . $vo_User->getUserId() . "-" . md5( OMEGAUP_MD5_SALT . $vo_User->getUserId( ) . time( ) );


        $vo_AuthT = new AuthTokens();
        $vo_AuthT->setUserId( $user_obj->getUserId( ) );
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
            $sm = self::getSessionManagerInstance();
            // ouat stands for omegaup auth token
            $sm->SetCookie('ouat', $s_AuthT, time()+60*60*24, '/');
        }

    }





    public function ThirdPartyLogin(  )
    {

        //RegisterSesion( $user_id );
    }



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

        $b_Success = RegisterSesion( $vo_User, $b_ReturnAuthToken );
    }

}