<?php

class Grader 
{
    private $graderUrl;
    
    public function Grader($graderUrl = NULL)
    {
        if($graderUrl === NULL)
        {
            $graderUrl = OMEGAUP_GRADER_URL;
        }
        
        $this->graderUrl = $graderUrl;    
    }
    
    public function Grade($runId)
    {
        // Initialize CURL
        $curl = curl_init();
        
        if($curl === FALSE)
        {            
            throw new Exception("curl_init failed: ". curl_error($curl));            
        }
        
        // Set URL
        curl_setopt($curl, CURLOPT_URL, $this->graderUrl); 
        
        // Get response from curl_exec() in string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        // Set certificate URL
        curl_setopt($curl, CURLOPT_SSLCERT, OMEGAUP_SSLCERT_URL);
        
        // Set certifiate to verify peer with
        curl_setopt($curl, CURLOPT_CAINFO, OMEGAUP_CACERT_URL);

        // Don't check the common name (CN) attribute
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                       
        // Set curl HTTP header
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        
        // Set curl Post data
        curl_setopt($curl, CURLOPT_POSTFIELDS, "{\"id\":$runId}");
        
        // Execute call
	$content = curl_exec($curl);

        // Close curl
	curl_close($curl);

	if(!$content)
        {            
            throw new Exception("curl_exec failed: " . curl_error($curl) . " ". curl_errno($curl));
        }
	else if ($content !== '{"status":"ok"}')
	{
            throw new Exception("Call to grader failed: '$content'");
	}
    }
}

?>
