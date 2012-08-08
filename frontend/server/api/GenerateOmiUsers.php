<?php

require_once("ApiHandler.php");

require_once(SERVER_PATH ."/libs/ApiException.php");

class GenerateOmiUsers extends ApiHandler 
{
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
        $contestant->setPassword(md5($password));
        $contestant->setSolved(0);
        $contestant->setSubmissions(0);
        UsersDAO::save($contestant);                      
    }
        
    protected function RegisterValidatorsToRequest() 
    {
        if (!Authorization::IsSystemAdmin($this->_user_id))
        {   
            throw new ApiException(ApiHttpErrors::forbiddenSite("Unauthorized."));
        }
        
    }
           
    
    protected function GenerateResponse() 
    {
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
              
              $this->CreateUser($username, $password);
          }
          
          if ($k == "SON")
          {
             for($i=5; $i<=8; $i++)
             {
                $username = $k . "-" . $i;
                $password = $this->rand_string(6);

                $this->addResponse($username, $password);

                $this->CreateUser($username, $password);
             }
          }
      }
      
      // Exceptions:
      
      
        
    }
    
}
?>
