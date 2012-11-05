<?php





class ContestController extends Controller
{

/*

finish_time
start_time
window_length
alias
scoreboard
points_decay_factor
submissions_gap
partial_score
feedback
penalty_time_start
penalty_calc_policy
public
private_users
problems
*/

    public function NewContest(  )
    {

        // Create and populate a new Contests object
        $contest = new Contests();
        
        if(is_null(RequestContext::get("public"))){
            $contest->setPublic(0);

        }else{
            $contest->setPublic(RequestContext::get("public"));
        }

        
        
        $contest->setTitle(RequestContext::get("title"));
        $contest->setDescription(RequestContext::get("description"));        
        $contest->setStartTime(gmdate('Y-m-d H:i:s', RequestContext::get("start_time")));        
        $contest->setFinishTime(gmdate('Y-m-d H:i:s', RequestContext::get("finish_time")));
        $contest->setWindowLength(RequestContext::get("window_length") == "NULL" ? NULL : RequestContext::get("window_length"));
        $contest->setDirectorId($this->_user_id);        
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
                throw new ApiException( ApiHttpErrors::duplicatedEntryInDatabase("alias"), $e);    
            }
            else
            {
               throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
            }
        }
        
        Logger::log("New Contest Created: ". RequestContext::get('alias'));
    }

}