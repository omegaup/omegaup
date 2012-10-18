<?php


class Controller
{
    protected $validator;

    protected $current_user_id;

    protected $current_user_obj;

    private static $_sessionManager;

    public static function getSessionManagerInstance()
    {
        if(is_null(self::$_sessionManager))
        {
            self::$_sessionManager = new SessionManager();
        }
        
        return self::$_sessionManager;
    }


}




class UserController extends Controller
{


    private function FindByEmail( $email )
    {
        $email_query = new Emails( );
        $email_query->setEmail( $email );
        $result = EmailsDAO::search( $email_query );
        
        if( sizeof( $result ) == 0)
        {
            return NULL;
        }

        ASSERT( sizeof( $result ) == 1 );
        return UsersDAO::getByPK( $result[0]->getUserId( ) );
    }

    /**
      * Description:
      *     All we know from google login is the email.
      *
      *
      **/
    public function LoginViaGoogle( $email )
    {
        $user = $this->FindByEmail( $email );

        if( is_null( $user ) )
        {
            //user does not exist in omegaup
            $this->NewUser( $email );
        }

        $this->RegisterSesion( $user );
    }


    public function NewUser( $email )
    {
        //create user
        $this_user  = new Users();
        $this_user->setUsername( $email );
        $this_user->setSolved( 0 );
        $this_user->setSubmissions( 0 );


        DAO::transBegin();
        //save this user
        try
        {
            UsersDAO::save( $this_user );

        }
        catch(Exception $e)
        {
            DAO::transRollback( );
            return false;
        }

        //create email
        $this_user_email = new Emails();
        $this_user_email ->setUserId( $this_user->getUserId() );
        $this_user_email ->setEmail( $email );

        //save this user
        try
        {
            EmailsDAO::save( $this_user_email );
        }
        catch(Exception $e)
        {
            DAO::transRollback( );
            return false;
        }

        DAO::transEnd();
        return $this_user;
    }


    private function UnRegisterSesion( AuthTokens $auth_token )
    {

        try{
            AuthTokensDAO::delete( $auth_token );
            
        }catch(Exception $e){
            
        }

    }


    private function RegisterSesion( Users $user_obj )
    {
        //find if this user has older sessions
         $auth_token = new AuthTokens( );
         $auth_token->setUserId( $user_obj->getUserId( ) );

        //erase them

        $auth_str = time( ) . "-" . $user_obj->getUserId() . "-" . md5( OMEGAUP_MD5_SALT . $user_obj->getUserId( ) . time( ) );


        $temp_auth_token = new AuthTokens();
        $temp_auth_token->setUserId( $user_obj->getUserId( ) );
        $temp_auth_token->setToken( $auth_str );

        try
        {
            AuthTokensDAO::save( $temp_auth_token );
        }
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation( ), $e);
        }

        $sm = self::getSessionManagerInstance();

         // ouat stands for omegaup auth token
        $sm->SetCookie('ouat', $auth_str, time()+60*60*24, '/');
    }


    public function LoginViaFacebook( $email )
    {
        $this->FindByEmail( $email );
    }

    public function Find( $user_or_email_or_fbid )
    {
        
    }

    public function Login( $user_or_email, $password )
    {
        
    }
}



class Validators
{



}