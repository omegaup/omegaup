<?php

/**
 * ContestsFactory
 *
 * @author joemmanuel
 */

require_once SERVER_PATH.'/controllers/contest.controller.php';

class ContestsFactory {
    
    public static function setContestContext($title = null, $public = 1, Users $contestDirector = null){
        
        if (is_null($contestDirector)){
            $contestDirector = UsersFactory::createUser();
        }
        
        if (is_null($title)){
            $title = Utils::CreateRandomString();       
        }
        
        // Set context
        RequestContext::set("title", $title);
        RequestContext::set("description", "description");
        RequestContext::set("start_time", Utils::GetPhpUnixTimestamp() - 60*60);
        RequestContext::set("finish_time", Utils::GetPhpUnixTimestamp() + 60*60);
        RequestContext::set("window_length", null);
        RequestContext::set("public", 1);
        RequestContext::set("alias", substr($title, 0, 20));
        RequestContext::set("points_decay_factor", ".02");
        RequestContext::set("partial_score", "0");
        RequestContext::set("submissions_gap", "0");
        RequestContext::set("feedback", "yes");
        RequestContext::set("penalty", 100);
        RequestContext::set("scoreboard", 100);
        RequestContext::set("penalty_time_start", "contest");
        RequestContext::set("penalty_calc_policy", "sum");
        
        return array(
            "title" => $title,
            "contestDirector" => $contestDirector,
            "alias" => RequestContext::get("alias")
        );                
    }
    
    public static function createContest($title = null, $public = 1, Users $contestDirector = null){
        
        $contestContext = self::setContestContext($title, $public, $contestDirector);
        
        $sc = new ContestController();
        $sc->current_user_id = $contestContext["contestDirector"]->getUserId();
        $sc->current_user_obj = $contestContext["contestDirector"];
        $sc->create();
        
        return array ("context" => $contestContext);
    }    
}

