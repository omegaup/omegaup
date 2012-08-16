<?php

require_once("ApiHandler.php");
require_once("AddUserToPrivateContest.php");
require_once(SERVER_PATH ."/libs/ApiException.php");

class GenerateOmiUsers extends ApiHandler 
{
    private $change_password = false;
    private $contest_to_add = null;
    
    private function rand_string( $length ) 
    {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	

	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}

	return $str;
    }
    
    private function CreateUser($username, $password)
    {
        // Check if user already exists
        $user_key = new Users();
        $user_key->setUsername($username);        
        $contestants = UsersDAO::search($user_key);
        
        if(sizeof($contestants) === 1)
        {
            // If exists, just get it 
            $contestant = $contestants[0];
        }
        else
        {        
            // If doesn't exists, create a new one
            $contestant = new Users();
        }        
        
        $contestant->setUsername($username);
        
        if ($this->change_password == true)
        {
            $contestant->setPassword(md5($password));
        }
        
        $contestant->setSolved(0);
        $contestant->setSubmissions(0);
        UsersDAO::save($contestant);            
        
        return $contestant;
    }
        
    private function AddUserToPrivateContest($user_id)
    {
        if (!is_null($this->contest_to_add))
        {            
            $userAgregator = new AddUserToPrivateContest();
	
            RequestContext::set("user_id", $user_id);
       
            $ret = $userAgregator->ExecuteApi();      
	}
    }
    
    protected function RegisterValidatorsToRequest() 
    {
        if (!Authorization::IsSystemAdmin($this->_user_id))
        {   
            throw new ApiException(ApiHttpErrors::forbiddenSite("Unauthorized."));
        }
        
        if (!is_null(RequestContext::get("contest_alias")))
        {        
            ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByAlias($value);
                }, "Contest is invalid."))
            ->validate(RequestContext::get("contest_alias"), "contest_alias");
        }
        
    }
           
    
    protected function GenerateResponse() 
    {

        $this->change_password = RequestContext::get("change_password");
        
        if (!is_null(RequestContext::get("contest_alias")))
        {
            $this->contest_to_add = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
        }
        
	$keys = array(
              "AGS",
              "BC",
              "BCS",
              "CAM",
              "COAH",
              "COL",
              "CHI",
              "CHIH",
              "DF",
              "DUR",
              "GTO",
              "GRO",
              "HDG",
              "JAL",
              "MEX",
              "MICH",
              "MOR",
              "NAY",
              "NL",
              "OAX",
              "PUE",
              "QRO",
              "QROO",
              "SLP",
              "SIN",
              "SON",
              "TAB",
              "TAM",
              "TLAX",
              "VER",
              "YUC",
              "ZAC"
        ); 


        foreach($keys as $k)
        {
            for($i=1; $i<=4; $i++)
            {
                $username = $k . "-" . $i;
                $password = $this->rand_string(6);

                $this->addResponse($username, $password);

                // Create user
                $user = $this->CreateUser($username, $password);    
               
                // In case of contest given in request, add users to that contest
                $this->AddUserToPrivateContest($user->getUserId());
            }

            // El estado sede tiene 4 usuarios más
            if ($k == "SON")
            {
               for($i=5; $i<=8; $i++)
               {
                  $username = $k . "-" . $i;
                  $password = $this->rand_string(6);

                  $this->addResponse($username, $password);

                  $user = $this->CreateUser($username, $password);
                  
                  // In case of contest given in request, add users to that contest
                  $this->AddUserToPrivateContest($user->getUserId());
               }
            }
        }

        // Exceptions:
      
      
        
    }
    
}
?>
