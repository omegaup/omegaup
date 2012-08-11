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

class UpdateContest extends ApiHandler
{
    private $private_users_list;
    private $problems;
    private $problems_id;
    private $hasPrivateUsers = false;
    private $contest;
    
    protected function RegisterValidatorsToRequest()
    {  
                        
        // Alias is required to know which contest to edit
        if (RequestContext::get("contest_alias") == null)
        {
            throw new ApiException( ApiHttpErrors::invalidParameter("Contest alias should be specified.") );    
        }
        
        // Get the contest from the DB
        try
        {
            $this->contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
        }
        catch(Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );
        }
        if ($this->contest == null)
        {
            throw new ApiException( ApiHttpErrors::invalidParameter("Contest alias specified does not exists.") );
        }
        
        
        // Is the user authorized?
        if (!Authorization::IsContestAdmin($this->_user_id, $this->contest))
        {
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }
           
        if (!is_null(RequestContext::get("title")))
        {
            ValidatorFactory::stringNotEmptyValidator()->validate(
                    RequestContext::get("title"),
                    "title");        
        }
                
        if (!is_null(RequestContext::get("description")))
        {
            ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("description"),
                "description");        
        }

        
	/*	
        ValidatorFactory::dateRangeValidator(
                RequestContext::get("start_time"), 
                RequestContext::get("finish_time"))
		->validate(RequestContext::get("start_time"), "start_time");
	 */
        
        // Calculate contest length:
        if (!is_null(RequestContext::get("finish_time")) && !is_null(RequestContext::get("start_time")))
        {
            $contest_length = RequestContext::get("finish_time") - RequestContext::get("start_time");
        }
        else
        {
            $contest_length = $this->contest->getFinishTime() - $this->contest->getStartTime();
        }

        // Window_length is optional
        if(!is_null(RequestContext::get("window_length")) && RequestContext::get("window_length") != "NULL")
        {
            ValidatorFactory::numericRangeValidator(
                    0, 
                    floor($contest_length)/60)
                    ->validate(RequestContext::get("window_length"), "window_length");
        }

        if (!is_null(RequestContext::get("public")))
        {
            ValidatorFactory::numericValidator()->validate(
                    RequestContext::get("public"),
                    "public");                
        }
                
        if (!is_null(RequestContext::get("scoreboard")))
        {
            ValidatorFactory::numericRangeValidator(0, 100)->validate(
                    RequestContext::get("scoreboard"), 
                    "scoreboard");
        }

        if (!is_null(RequestContext::get("points_decay_factor")))
        {
            ValidatorFactory::numericRangeValidator(0, 1)->validate(
                    RequestContext::get("points_decay_factor"), "points_decay_factor");
        }
        
        if (!is_null(RequestContext::get("partial_score")))
        {
            ValidatorFactory::numericValidator()->validate(
                    RequestContext::get("partial_score"), "partial_score");
        }
        
        if (!is_null(RequestContext::get("submissions_gap")))
        {        
            ValidatorFactory::numericRangeValidator(0, $contest_length)
                    ->validate(RequestContext::get("submissions_gap"), "submissions_gap");
        }
        
        if (!is_null(RequestContext::get("feedback")))
        {
            ValidatorFactory::enumValidator(array("no", "yes", "partial"))
                    ->validate(RequestContext::get("feedback"), "feedback");
        }

	if (!is_null(RequestContext::get("penalty")))
        {
            ValidatorFactory::numericRangeValidator(0, INF)
                    ->validate(RequestContext::get("penalty"), "penalty");
        }
	
        if (!is_null(RequestContext::get("penalty_time_start")))
        {
            ValidatorFactory::enumValidator(array("contest", "problem", "none"))
                    ->validate(RequestContext::get("penalty_time_start"), "penalty_time_start");
        }
        
        if (!is_null(RequestContext::get("penalty_calc_policy")))
        {
            ValidatorFactory::enumValidator(array("sum", "max"))
                    ->validate(RequestContext::get("penalty_calc_policy"), "penalty_calc_policy");
        }
                
        
        // Validate private_users request, only if the contest is private            
        // If the contest is private, it may contain private users
        if(!is_null(RequestContext::get("public")) && RequestContext::get("public") == 0 && !is_null(RequestContext::get("private_users")))
        {               
            // Validate that the request is well-formed
            $this->private_users_list = json_decode(RequestContext::get("private_users"));
            if (is_null($this->private_users_list))
            {
               throw new ApiException( ApiHttpErrors::invalidParameter("private_users is malformed") );    
            }                

            // Validate that all users exists in the DB
            foreach($this->private_users_list as $userkey)
            {
                if (is_null(UsersDAO::getByPK($userkey)))
                {
                   throw new ApiException( ApiHttpErrors::invalidParameter("private_users contains a user that doesn't exists") );    
                }
            }  
            
            // Turn on flag to add private users later
            $this->hasPrivateUsers = true;
        }

        // Problems is optional
        if (!is_null(RequestContext::get('problems')))
        {
            $this->problems = array();
            $this->problems_id = array();
            
            foreach (json_decode(RequestContext::get('problems')) as $problem) 
            {
                    $p = ProblemsDAO::getByAlias($problem->problem);
                    array_push($this->problems_id, $p->getProblemId());
                    
                    $this->problems[$p->getProblemId()] = array(
                            'alias' => $problem->problem,
                            'points' => $problem->points);                    
            }
        }
    }       
   
    
    protected function GenerateResponse() 
    {                
        
        // Update contest DAO                
        if (!is_null(RequestContext::get("public")))
        {
            $this->contest->setPublic(RequestContext::get("public"));
        }
        
        if (!is_null(RequestContext::get("title")))
        {
            $this->contest->setTitle(RequestContext::get("title"));
        }
        
        if (!is_null(RequestContext::get("description")))
        {
            $this->contest->setDescription(RequestContext::get("description"));        
        }
        
        if (!is_null(RequestContext::get("start_time")))
        {
            $this->contest->setStartTime(gmdate('Y-m-d H:i:s', RequestContext::get("start_time")));        
        }
        
        if (!is_null(RequestContext::get("finish_time")))
        {        
            $this->contest->setFinishTime(gmdate('Y-m-d H:i:s', RequestContext::get("finish_time")));
        }
        
        if (!is_null(RequestContext::get("window_length")))
        {
            $this->contest->setWindowLength(RequestContext::get("window_length") == "NULL" ? NULL : RequestContext::get("window_length"));
        }
                                
        if (!is_null(RequestContext::get("scoreboard")))
        {
            $this->contest->setScoreboard(RequestContext::get("scoreboard"));
        }
        
        if (!is_null(RequestContext::get("points_decay_factor")))
        {
            $this->contest->setPointsDecayFactor(RequestContext::get("points_decay_factor"));
        }
        
        if (!is_null(RequestContext::get("partial_score")))
        {
            $this->contest->setPartialScore(RequestContext::get("partial_score"));
        }
        
        if (!is_null(RequestContext::get("submissions_gap")))
        {
            $this->contest->setSubmissionsGap(RequestContext::get("submissions_gap"));
        }
        
        if (!is_null(RequestContext::get("feedback")))
        {
            $this->contest->setFeedback(RequestContext::get("feedback"));
        }
        
        if (!is_null(RequestContext::get("penalty")))
        {
            $this->contest->setPenalty(max(0, intval(RequestContext::get("penalty"))));
        }
        
        if (!is_null(RequestContext::get("penalty_time_start")))
        {            
            $this->contest->setPenaltyTimeStart(RequestContext::get("penalty_time_start"));
        }
        
        if (!is_null(RequestContext::get("penalty_calc_policy")))
        {
            $this->contest->setPenaltyCalcPolicy(RequestContext::get("penalty_calc_policy"));                
        }
        
        // Push changes
        try
        {
            // Begin a new transaction
            ContestsDAO::transBegin();
            
            // Save the contest object with data sent by user to the database
            ContestsDAO::save($this->contest);
                        
            // If the contest is private, add the list of allowed users
            if (!is_null(RequestContext::get("public")) && RequestContext::get("public") == 0 && $this->hasPrivateUsers)
            {
                // Get current users
                $cu_key = new ContestsUsers( array ("contest_id" => $this->contest->getContestId()));
                $current_users = ContestsUsersDAO::search($cu_key);
                $current_users_id = array();
                
                foreach($current_users as $cu)
                {
                    array_push($current_users_id, $current_users->getUserId());
                }                
                
                // Check who needs to be deleted and who needs to be added
                $to_delete = array_diff($current_users_id, $this->private_users_list);
                $to_add = array_diff($this->private_users_list, $current_users_id);                                
                               
                // Add users in the request
                foreach($to_add as $userkey)
                {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ContestsUsers( array(
                        "contest_id" => $this->contest->getContestId(),
                        "user_id" => $userkey,
                        "access_time" => "0000-00-00 00:00:00",
                        "score" => 0,
                        "time" => 0
                    ));                    
                    
                    // Save the relationship in the DB
                    ContestsUsersDAO::save($temp_user_contest);
                }
                
                // Delete users 
                foreach($to_delete as $userkey)
                {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ContestsUsers( array(
                        "contest_id" => $this->contest->getContestId(),
                        "user_id" => $userkey,                       
                    ));                    
                    
                    // Delete the relationship in the DB
                    ContestsUsersDAO::delete(ContestProblemsDAO::search($temp_user_contest));
                }
            }

            if (!is_null(RequestContext::get('problems')))
            {        
                // Get current problems
                $p_key = new Problems(array( "contest_id" => $this->contest->getContestId()));
                $current_problems = ProblemsDAO::search($p_key);
                $current_problems_id = array();                
                
                foreach($current_problems as $p)
                {
                    array_push($current_problems_id, $p->getProblemId());
                }
                                                
                // Check who needs to be deleted and who needs to be added
                $to_delete = array_diff($current_problems_id, $this->problems_id);
                $to_add = array_diff($this->problems_id, $current_problems_id);      
                
                foreach ($to_add as $problem)
                {
                    $contest_problem = new ContestProblems(array(
                            'contest_id' => $this->contest->getContestId(),
                            'problem_id' => $problem,
                            'points' => $this->problems[$problem]['points']
                    ));

                    ContestProblemsDAO::save($contest_problem);
                }
                
                foreach ($to_delete as $problem)
                {
                    $contest_problem = new ContestProblems(array(
                            'contest_id' => $this->contest->getContestId(),
                            'problem_id' => $problem,                            
                    ));
                    
                    ContestProblemsDAO::delete(ContestProblemsDAO::search($contest_problem));
                }
            }
            
            // End transaction 
            ContestsDAO::transEnd();

        }catch(Exception $e)
        {   
            // Operation failed in the data layer, rollback transaction 
            ContestsDAO::transRollback();
                       
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
        }
        
        // Happy ending
        $this->addResponse("status", "ok");
        
        Logger::log("Contest updated (alias): ". RequestContext::get('contest_alias'));        
    }    
}

