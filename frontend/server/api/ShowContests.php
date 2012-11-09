<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 *
 *
 * */
require_once("ApiHandler.php");


class ShowContests extends ApiHandler {
    

    protected function RegisterValidatorsToRequest() 
    {
        return true;
    }



    protected function CheckAuthToken()
    {                
        // Call parent CheckAuthToken just to crack user credentials
        try
        {
            parent::CheckAuthToken();
        }
        catch(ApiException $e)
        {
            if ($e->getArrayMessage() == ApiHttpErrors::invalidAuthToken())
            {
                // A logged user can view the list of contests
                return;
            }
        }    
    }



    protected function GenerateResponse() {

        // Create array of relevant columns
        $relevant_columns = array("contest_id", "title", "description", "start_time", "finish_time", "public", "alias", "director_id", "window_length");

        try
        {                
            // Get all contests using only relevan columns
            $contests = ContestsDAO::getAll(NULL, NULL, 'contest_id', "DESC", $relevant_columns);
        }
        catch(Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        

        // DAO requires contest_id as relevant column but we don't want to expose it
        array_shift($relevant_columns);
        
        /**
         * Ok, lets go 1 by 1, and if its public, show it,
         * if its not, check if the user has access to it.
         * */
        $addedContests = array();

        foreach ($contests as $c) 
        { 
            // At most we want 10 contests @TODO paginar correctamente
            if ($addedContests === 10)
            {
                break;
            }

            if ($c->getPublic()) 
            {
                $c->toUnixTime();           
                
                $contestInfo = $c->asFilteredArray($relevant_columns);
                $contestInfo["duration"] = (is_null($c->getWindowLength()) ? 
                        $c->getFinishTime() - $c->getStartTime() : ($c->getWindowLength()*60));
                
                $addedContests[] = $contestInfo;
                continue;
            }

            /*
             * Ok, its not public, lets se if we have a 
             * valid user
             * */	
            if ($this->_user_id === null)
            {
                continue;
            }

            /**
             * Ok, i have a user. Can he see this contest ?
             * */
            try
            {
                $r = ContestsUsersDAO::getByPK($this->_user_id, $c->getContestId());
            }
            catch(Exception $e)
            {
                throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
            }

            // Admins can see all contests
            if ($r === null && !Authorization::IsSystemAdmin($this->_user_id)) 
            {
                /**
                 * Nope, he cant .
                 * */
                continue;
            }

            /**
             * He can see it !
             * 
             * */
            $contestInfo = $c->asFilteredArray($relevant_columns);
            $contestInfo["duration"] = (is_null($c->getWindowLength()) ? 
                        $c->getFinishTime() - $c->getStartTime() : ($c->getWindowLength()*60));
                
            $addedContests[] = $contestInfo;
            
	}
        
        $this->addResponse('contests', $addedContests);
        $this->addResponse('length', count($addedContests));
    }


}

?>
