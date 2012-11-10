<?php

/**
 * ContestController
 * 
 */
require_once SERVER_PATH.'/controllers/controller.php';
require_once SERVER_PATH.'/libs/RequestContext.php';
require_once SERVER_PATH.'/libs/validators.php';

class ContestController extends Controller
{

    private $private_users_list;
    private $hasPrivateUsers;
    private $problems;
    
    
    /**
     * create new contest
     */
    public function create(){        
        
        // Validate request
        $this->validateCreateRequest();
        
        // Create and populate a new Contests object
        $contest = new Contests();              
                
        $contest->setPublic(RequestContext::get("public")); 
        $contest->setTitle(RequestContext::get("title"));
        $contest->setDescription(RequestContext::get("description"));        
        $contest->setStartTime(gmdate('Y-m-d H:i:s', RequestContext::get("start_time")));        
        $contest->setFinishTime(gmdate('Y-m-d H:i:s', RequestContext::get("finish_time")));
        $contest->setWindowLength(RequestContext::get("window_length") == "NULL" ? NULL : RequestContext::get("window_length"));
        $contest->setDirectorId($this->current_user_id);        
        $contest->setRerunId(0); // NYI
        
        $contest->setAlias(RequestContext::get("alias"));
        $contest->setScoreboard(RequestContext::get("scoreboard"));
        $contest->setPointsDecayFactor(RequestContext::get("points_decay_factor"));
        $contest->setPartialScore(RequestContext::get("partial_score"));
        $contest->setSubmissionsGap(RequestContext::get("submissions_gap"));
        $contest->setFeedback(RequestContext::get("feedback"));
        $contest->setPenalty(max(0, intval(RequestContext::get("penalty"))));
        $contest->setPenaltyTimeStart(RequestContext::get("penalty_time_start"));
        $contest->setPenaltyCalcPolicy(RequestContext::get("penalty_calc_policy"));
        
        if (!is_null(RequestContext::get("show_scoreboard_after")))
        {
            $contest->setShowScoreboardAfter(RequestContext::get("show_scoreboard_after"));
        }
        else
        {
            $contest->setShowScoreboardAfter("1");
        }
        
        
        // Push changes
        try
        {
            // Begin a new transaction
            ContestsDAO::transBegin();
            
            // Save the contest object with data sent by user to the database
            ContestsDAO::save($contest);
                        
            // If the contest is private, add the list of allowed users
            if (RequestContext::get("public") == 0 && $this->hasPrivateUsers)
            {
                foreach($this->private_users_list as $userkey)
                {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ContestsUsers( array(
                        "contest_id" => $contest->getContestId(),
                        "user_id" => $userkey,
                        "access_time" => "0000-00-00 00:00:00",
                        "score" => 0,
                        "time" => 0
                    ));                    
                    
                    // Save the relationship in the DB
                    ContestsUsersDAO::save($temp_user_contest);
                }
            }

            if (!is_null(RequestContext::get('problems')))
            {                
                foreach ($this->problems as $problem)
                {
                    $contest_problem = new ContestProblems(array(
                            'contest_id' => $contest->getContestId(),
                            'problem_id' => $problem['id'],
                            'points' => $problem['points']
                    ));

                    ContestProblemsDAO::save($contest_problem);
                }
            }
            
            // End transaction transaction
            ContestsDAO::transEnd();

        }catch(Exception $e)
        {   
            // Operation failed in the data layer, rollback transaction 
            ContestsDAO::transRollback();
            
            // Alias may be duplicated, 1062 error indicates that
            if(strpos($e->getMessage(), "1062") !== FALSE)
            {
                throw new DuplicatedEntryInDatabaseException("alias already exists. Please choose a different alias.", $e);
            }
            else
            {
               throw new InvalidDatabaseOperation($e);                
            }
        }
        
        Logger::log("New Contest Created: ". RequestContext::get('alias'));    
        
        return array ("status" => "ok");
    }
    
    /**
     * validateCreateRequest
     * 
     * @throws InvalidParameterException
     */
    private function validateCreateRequest(){
        
        Validators::isStringNonEmpty(RequestContext::get("title"), "title");
        Validators::isStringNonEmpty(RequestContext::get("description"), "description", false);
        
        Validators::isNumber(RequestContext::get("start_time"), "start_time");
        Validators::isNumber(RequestContext::get("finish_time"), "finish_time");
        if (RequestContext::get("start_time") > RequestContext::get("finish_time")){
            throw new InvalidParameterException("start_time cannot be after finish_time");
        }
        
        // Calculate contest length:
        $contest_length = RequestContext::get("finish_time") - RequestContext::get("start_time");

        // Window_length is optional        
        Validators::isNumberInRange(
                RequestContext::get("window_length"), 
                "window_length", 
                0, 
                floor($contest_length)/60, 
                false
            );
        
        Validators::isInEnum(RequestContext::get("public"), "public", array("0", "1"));
        Validators::isStringOfMaxLength(RequestContext::get("alias"), "alias", 32);
        Validators::isNumberInRange(RequestContext::get("scoreboard"), "scoreboard", 0, 100);
        Validators::isNumberInRange(RequestContext::get("points_decay_factor"), "points_decay_factor", 0, 1);
        Validators::isInEnum(RequestContext::get("partial_score"), "partial_score", array("0", "1"));
        Validators::isNumberInRange(RequestContext::get("submissions_gap"), "submissions_gap", 0, $contest_length);
                
        Validators::isInEnum(RequestContext::get("feedback"), "feedback", array("no", "yes", "partial"), false);
        Validators::isInEnum(RequestContext::get("penalty_time_start"), "penalty_time_start", array("contest", "problem", "none"), false);
        Validators::isInEnum(RequestContext::get("penalty_calc_policy"), "penalty_calc_policy", array("sum", "max"), false);
        
        
        if(RequestContext::get("public") == 0 && !is_null(RequestContext::get("private_users")))
        {            
            // Validate that the request is well-formed
            //  @todo move $this
            $this->private_users_list = json_decode(RequestContext::get("private_users"));
            if (is_null($this->private_users_list))
            {
               throw new InvalidParameterException("private_users".Validators::IS_INVALID);
            }                

            // Validate that all users exists in the DB
            foreach($this->private_users_list as $userkey)
            {
                if (is_null(UsersDAO::getByPK($userkey)))
                {
                   throw new InvalidParameterException("private_users contains a user that doesn't exists");    
                }
            }  
            
            // Turn on flag to add private users later
            $this->hasPrivateUsers = true;
        }
        
        // Problems is optional
        if (!is_null(RequestContext::get('problems')))
        {
            $this->problems = array();

            foreach (json_decode(RequestContext::get('problems')) as $problem) {
                    $p = ProblemsDAO::getByAlias($problem->problem);
                    array_push($this->problems, array(
                            'id' => $p->getProblemId(),
                            'alias' => $problem->problem,
                            'points' => $problem->points
                    ));
            }
        }
        
        Validators::isInEnum(RequestContext::get("show_scoreboard_after"), "show_scoreboard_after", array("0", "1"), false);        
    }
     
}