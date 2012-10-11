<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
GET /clarifications/contest/:id
Regresa TODAS las clarificaciones de un concurso en particular, a las cuales el usuario puede ver (equivale a las que el personalmente mandó más todas las clarificaciones del problema marcadas como globales)
 *
 * */

require_once("ApiHandler.php");

class ShowClarificationsInContest extends ApiHandler
{
    
    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ContestsDAO::getByAlias($value);
            }, "Contest requested is invalid."))
        ->validate(RequestContext::get("contest_alias"), "contest_alias");
            
        // Check offset, is optional
        if (!is_null(RequestContext::get("offset")))
        {
            ValidatorFactory::numericValidator()->validate(RequestContext::get("offset"), "offset");
            $this->offset = RequestContext::get("offset");
	}
        
        // Check rowcount, is optional
        if (!is_null(RequestContext::get("rowcount")))
        {
            ValidatorFactory::numericValidator()->validate(RequestContext::get("rowcount"), "rowcount");
            $this->rowcount = RequestContext::get("rowcount");
	}                
    }   
   

    protected function GenerateResponse() 
    {
       // Create array of relevant columns
	$relevant_columns = array("clarification_id", "problem_alias", "message", "answer", "time", "public");
        
        try
        {
            //Get all public clarifications
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
        }
        catch(Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        $public_clarification_mask = new Clarifications ( array (
           "public" => '1',
           "contest_id" => $contest->getContestId()
	));

	$is_contest_director = Authorization::IsContestAdmin($this->_user_id, $contest);
        
        // If user is the contest director, get all private clarifications        
        if($is_contest_director)
        {                        
            // Get all private clarifications 
            $private_clarification_mask = new Clarifications ( array (
               "public" => '0',
               "contest_id" => $contest->getContestId()
            )); 
        }        
        else
        {        
            // Get private clarifications of the user 
            $private_clarification_mask = new Clarifications ( array (
               "public" => '0',
               "contest_id" => $contest->getContestId(),
               "author_id" => $this->_user_id
            ));
        }       
        
        //@todo This query could be merged and optimized 
        // Get our clarifications given the masks
        try
        {               
            $clarifications_public = ClarificationsDAO::search($public_clarification_mask);
            $clarifications_private = ClarificationsDAO::search($private_clarification_mask);            
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );        
	}

        $clarifications_array = array();
        
        // Filter each Public clarification and add it to the response        
        foreach($clarifications_public as $clarification)
        {
            $clar = $clarification->asFilteredArray($relevant_columns);
            $clar['can_answer'] = $is_contest_director;

            // Add author in case of contest_director
            if ($is_contest_director)
            {
                try
                {
                    $author_user = UsersDAO::getByPK($clarification->getAuthorId());
                    $clar['author'] = $author_user->getUsername();
                }
                catch(Exception $e)
                {
                    throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);
                }                    
            }

            array_push($clarifications_array, $clar);
        }
         
        // Filter each Private clarification and add it to the response
        foreach($clarifications_private as $clarification)
	{
            $clar = $clarification->asFilteredArray($relevant_columns);
            $clar['can_answer'] = $is_contest_director;

            // Add author in case of contest_director
            if ($is_contest_director)
            {
                try
                {
                    $author_user = UsersDAO::getByPK($clarification->getAuthorId());
                    $clar['author'] = $author_user->getUsername();
                }
                catch(Exception $e)
                {
                    throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);
                }                    
            }

            array_push($clarifications_array, $clar);
        }
        
        // Sort final array by time
        usort($clarifications_array, function($a, $b) 
            {
                // First, let's order by answer
                $a_answered = strlen($a['answer']) > 0;
                $b_answered = strlen($b['answer']) > 0;
                
                // If they have the same status, check 
                if ($a_answered === $b_answered)
                {
                    $t1 = strtotime($a["time"]);
                    $t2 = strtotime($b["time"]);

                    if($t1 === $t2)
                        return 0;

                    return ($t1 > $t2) ? -1 : 1;             
                }
                
                // If a is not answered, it has priority
                if($a_answered === false)
                {
                    return -1;
                }
                else
                {
                    return 1;
                }                
            });
            
        // LIMIT the array    
        $clarifications_array_sliced = array_slice($clarifications_array, RequestContext::get("offset"), RequestContext::get("rowcount"), true);
            
        // Add response to array
        $this->addResponse('clarifications', $clarifications_array_sliced);
    }
}

?>
