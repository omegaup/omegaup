<?php


class SecurityTools
{

    public static function EncryptString( $unencrypted )
    {
        return $unencrypted;
    }

    public static function CompareEncryptedStrings( $encrypted_a, $encrypted_b )
    {
        return strcmp( $encrypted_a, $encrypted_b );
    }

}




class Controller
{
    protected $validator;

    protected $current_user_id;

    protected $current_user_obj;

    private static $_sessionManager;

    public static function getSessionManagerInstance( )
    {
        if ( is_null( self::$_sessionManager ) )
        {
            self::$_sessionManager = new SessionManager( );
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

    private function FindByUsername( $s_Username )
    {
        $vo_Query = new Users( array( 
            "username" => $s_Username
            ) );

        $a_Results = UsersDAO::search( $vo_Query );

        if ( sizeof( $a_Results ) != 1 )
        {
            return NULL;
        }

        return array_pop( $a_Results );
    }


    public function Create( $username, $email )
    {
        //create user
        $this_user  = new Users( );
        $this_user->setUsername( $username );
        $this_user->setSolved( 0 );
        $this_user->setSubmissions( 0 );

        DAO::transBegin( );

        try
        {
            //save this user
            UsersDAO::save( $this_user );

        }
        catch(Exception $e)
        {
            DAO::transRollback( );
            return false;
        }

        //create email
        $this_user_email = new Emails( );
        $this_user_email ->setUserId( $this_user->getUserId( ) );
        $this_user_email ->setEmail( $email );

        //save this user
        try
        {
            EmailsDAO::save( $this_user_email );
        }
        catch( Exception $e )
        {
            DAO::transRollback( );
            return false;
        }

        DAO::transEnd( );
        return $this_user;
    }



    /**
      *
      * Description:
      *     Tests a if a password is valid for a given user.
      *
      * Returns:
      *     Boolean
      *
      *
      **/
    public function TestPassword( $user_id = null, $email = null, $username = null, $password )
    {
        if( is_null( $user_id ) && is_null( $email ) && is_null( $username ) )
        {
            throw new Exception("You must provide either one of the following: user_id, email or username");
        }

        $vo_UserToTest = null;

        //find this user
        if( !is_null( $user_id ) )
        {
            $vo_UserToTest = USersDAO::getByPK( $user_id );
        }
        else if ( !is_null( $email ) )
        {
            $vo_UserToTest = $this->FindByEmail( );
        }
        else
        {
            $vo_UserToTest = $this->FindByUserName( );
        }


        if( is_null( $vo_UserToTest ) )
        {
            return false;
        }

        return SecurityTools::CompareEncryptedStrings(
                    SecurityTools::EncryptString( $password ),
                    $vo_UserToTest->getPassword( )
                );

    }
}


