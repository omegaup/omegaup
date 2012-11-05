<?php

/**
  * Description:
  *     
  *
  * Author:
  *     Alan Gonzalez alanboy@alanboy.net
  *
  **/
class SecurityTools
{

    public static function EncryptString( $unencrypted )
    {
        return $unencrypted;
    }

    public static function CompareEncryptedStrings( $encrypted_a, $encrypted_b )
    {
        Logger::log( "Comparing:" . $encrypted_a . "<->" . $encrypted_b );

        return strcmp( $encrypted_a, $encrypted_b ) == 0;
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



class Validators
{
    public static function isValidEmail( $s_Email = null )
    {
        if( is_null( $s_Email ) )
        {
            return false;
        }

        return filter_var( $s_Email, FILTER_VALIDATE_EMAIL );
    }

}



class UserController extends Controller
{


    public function FindByEmail( $email )
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



    public function FindByUsername( $s_Username )
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


    public function CreateProfile(  )
    {
        
    }



    /**
      * 
      * 
      * 
      */
    public function Create( $s_Email, $s_Username = null, $s_PlainPassword = null )
    {

        if( is_null( $s_Email ) )
        {
            throw new ApiException( "Must provide email" );
        }

        if( !Validators::isValidEmail( $s_Email ) )
        {
            throw new ApiException( "Invalid Email" );
        }

        if( is_null( $this->FindByEmail( $s_Email ) ) )
        {
            //Email already exists
            throw new ApiException( "Email alrady exists." );
        }

        //create user
        $vo_User  = new Users( );
        $vo_User->setSolved( 0 );
        $vo_User->setSubmissions( 0 );

        if ( is_null( $s_Username ) )
        {
            //@TODO change this
            $vo_User->setUsername( "$" . md5( $s_Email ) );
        }
        else
        {
            $vo_User->setUsername( $s_Username );
        }


        if ( is_null( $s_PlainPassword ) )
        {
            $vo_User->setPassword( NULL );
        }
        else
        {
            $vo_User->setPassword( SecurityTools::EncryptString( $s_PlainPassword ) );
        }

        DAO::transBegin( );

        // Create email
        $vo_Email = new Emails( );
        $vo_Email ->setUserId( $vo_User->getUserId( ) );
        $vo_Email ->setEmail( $s_Email );

        try
        {
            EmailsDAO::save( $vo_Email );

            $vo_User->setMainEmailId( $vo_Email->getEmailId( ) );

            UsersDAO::save( $vo_User );
        }
        catch( Exception $e )
        {
            DAO::transRollback( );
            throw new ApiException( "DB_ERROR", $e );
        }


        DAO::transEnd( );

        return $vo_User;

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
            Logger::warn("User X invalid login");
            return false;
        }

        return SecurityTools::CompareEncryptedStrings(
                    SecurityTools::EncryptString( $password ),
                    $vo_UserToTest->getPassword( )
                );

    }
}


