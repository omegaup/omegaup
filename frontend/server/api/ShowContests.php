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

class ShowContests extends ApiHandler {

    protected function CheckAuthorization() {
        // @todo this CheckAuthorization thing should be refactored 

        if (
                isset($_REQUEST["auth_token"])
        ) {

            /**
             * They sent me an auth token ! Lets look for it.
             * */
            $token = AuthTokensDAO::getByPK($_POST["auth_token"]);

            if ($token !== null) {
                /**
                 *
                 * Found it !
                 * */
                $this->user_id = $token->getUserId();
            } else {
                
                // We have an invalid auth token. Dying.            
               throw new ApiException( $this->error_dispatcher->invalidAuthToken() );
            }
        }
    }

    protected function GetRequest() {
        return true;
    }

    protected function GenerateResponse() {

        // Create array of relevant columns
        $relevant_columns = array("contest_id", "title", "description", "start_time", "finish_time", "public", "token", "director_id");

        // Get all contests using only relevan columns
        $contests = ContestsDAO::getAll(NULL, NULL, 'contest_id', "DESC", $relevant_columns);

        $this->response = array();

        /**
         * Ok, lets go 1 by 1, and if its public, show it,
         * if its not, check if the user has access to it.
         * */
        foreach ($contests as $c) {

            if (sizeof($this->response) == 10)
                break;

            if ($c->getPublic()) {
                array_push($this->response, $c->asFilteredArray($relevant_columns));
                continue;
            }

            /*
             * Ok, its not public, lets se if we have a 
             * valid user
             * */
            if ($this->user_id === null)
                continue;

            /**
             * Ok, i have a user. Can he see this contest ?
             * */
            $r = ContestsUsersDAO::getByPK($this->user_id, $c->getContestId());

            if ($r === null) {
                /**
                 * Nope, he cant .
                 * */
                continue;
            }

            /**
             * He can see it !
             * 
             * */
            array_push($this->response, $c->asFilteredArray($relevant_columns));
        }
    }


}

?>
