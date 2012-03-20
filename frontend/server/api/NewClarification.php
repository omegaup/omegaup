<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /contests/:id:/problem/new
 * Si el usuario tiene permisos de juez o admin, crea un nuevo problema para el concurso :id
 *
 * */

require_once("ApiHandler.php");
require_once("Mail.php");

class NewClarification extends ApiHandler
{
    
    protected function RegisterValidatorsToRequest()
    {    
        
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByAlias($value);
                }, "Contest is invalid."))
            ->validate(RequestContext::get("contest_alias"), "contest_alias");
            
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ProblemsDAO::getByAlias($value);
            }, "Problem requested is invalid."))
        ->validate(RequestContext::get("problem_alias"), "problem_alias");                  
                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("message"),
                "message");
        
        try
        {
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                        
            $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        }
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        // Is the combination contest_id and problem_id valid?        
        if (is_null(
                ContestProblemsDAO::getByPK($contest->getContestId(), 
                                            $problem->getProblemId())))
        {
           throw new ApiException(ApiHttpErrors::notFound());
        }
        
    }
    
    protected function GenerateResponse() 
    {
        
        // Populate a new Clarification object
        try
        {
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                        
            $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        }
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        $clarification = new Clarifications( array(
            "author_id" => $this->_user_id,
            "contest_id" => $contest->getContestId(),
            "problem_id" => $problem->getProblemId(),
            "message" => RequestContext::get("message"),
            "public" => '0'
        ));

        // Insert new Clarification
        try
        {            
            // Save the clarification object with data sent by user to the database
            ClarificationsDAO::save($clarification);            

        }catch(Exception $e)
        {              
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
        }
        
	$this->addResponse("clarification_id", $clarification->getClarificationId());

	try {
		if(!OMEGAUP_EMAIL_SEND_EMAILS){
			return;
		}

		// Get the contest director's email
		$director = UsersDAO::getByPK( $contest->getDirectorId() );
		$director_mail = EmailsDAO::getByPK($director->getMainEmailId());

		// Get the problem's owner email
		$owner = UsersDAO::getByPK( $problem->getAuthorId() );
		$owner_mail = EmailsDAO::getByPK( $owner->getMainEmailId() );

		// Generate the email body
		$body = "You have a new clarification message from contest `".$contest->getAlias()."` ";

		$body .= "\n\n\n";
		$body .= "Problem: " . $problem->getAlias() . "\n";
		$body .= "Submitter: 	" . $this->_user_id . "\n";
		$body .= "Clarification body: "	. "\n";
		$body .= "---------------------------------------------\n";
		$body .= RequestContext::get("message");
		$body .= "\n---------------------------------------------\n";		
		
		
		 $smtp = Mail::factory('smtp',
		   array (	 'host' => OMEGAUP_EMAIL_SMTP_HOST,
		     		 'port' => OMEGAUP_EMAIL_SMTP_PORT, 
				     'auth' => true,
				     'username' => OMEGAUP_EMAIL_SMTP_USER,
				     'password' => OMEGAUP_EMAIL_SMTP_PASSWORD));

		Logger::error("Sending email to director at " . $director_mail . " ...");
		
		$headers = array (
			'From' => "OmegaUp <". OMEGAUP_EMAIL_SMTP_FROM .">",
		   	'To' => $director_mail,
		   	'Subject' => "New clarification");
		
		
		 $mail = $smtp->send($director_mail, $headers, $body);

		if(PEAR::isError($mail)) {
			echo( $mail->getMessage() . "\n");
			Logger::error("Error while sending email to " . $director_mail);			
			Logger::error($mail->getMessage());
			
		}
		
		if ($owner_mail != $director_mail) {
			Logger::error("Sending email to problem owner at " . $owner_mail . " ...");
		
			$headers = array (
				'From' => "OmegaUp <". OMEGAUP_EMAIL_SMTP_FROM .">",
		   		'To' => $owner_mail,
			   	'Subject' => "New clarification");
		
		
			 $mail = $smtp->send($director_mail, $headers, $body);

			if(PEAR::isError($mail)) {
				echo( $mail->getMessage() . "\n");
				Logger::error("Error while sending email to " . $owner_mail);			
				Logger::error($mail->getMessage());
			
			}
		}

	} catch (Exception $e) {
		Logger::error($e);
	}

    }    
}

?>
