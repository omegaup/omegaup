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

    protected function GenerateResponse() {

        // Create array of relevant columns
        $relevant_columns = array("contest_id", "title", "description", "start_time", "finish_time", "public", "alias", "director_id");

        // Get all contests using only relevan columns
        $contests = ContestsDAO::getAll(NULL, NULL, 'contest_id', "DESC", $relevant_columns);
        

        /**
         * Ok, lets go 1 by 1, and if its public, show it,
         * if its not, check if the user has access to it.
         * */
        $addedContests = 0;
        foreach ($contests as $c) 
        {
            // At most we want 10 contests
            if ($addedContests === 10)
            {
                break;
            }

            if ($c->getPublic()) 
            {
                $this->addResponse($addedContests, $c->asFilteredArray($relevant_columns));                
                $addedContests++;
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
            $r = ContestsUsersDAO::getByPK($this->_user_id, $c->getContestId());

            if ($r === null) 
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
            $this->addResponse($addedContests, $c->asFilteredArray($relevant_columns));
            $addedContests++;
        }
    }


}

?>
