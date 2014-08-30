<?php

/**
 *  GroupScoreboardController
 *
 * @author joemmanuel
 */

class GroupScoreboardController extends Controller {
	
	/**
	 * Validate group scoreboard request
	 * 
	 * @param Request $r
	 */
	private static function validateGroupScoreboard(Request $r) {
		GroupController::validateGroup($r);
		
		Validators::isValidAlias($r["scoreboard_alias"], "scoreboard_alias");		
		try {
			$r["scoreboards"] = GroupsScoreboardsDAO::search(new GroupsScoreboards(array(
				"alias" => $r["scoreboard_alias"]
			)));
		} catch (Exception $ex) {
			throw new InvalidDatabaseOperationException($ex);
		}
		
		if (is_null($r["scoreboards"]) || count($r["scoreboards"]) === 0 || is_null($r["scoreboards"][0])) {
			throw new InvalidParameterException("parameterNotFound", "Scoreboard");
		}
		
		$r["scoreboard"] = $r["scoreboards"][0];
	}
	
	/**
	 * Validates that group alias and contest alias do exist
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws InvalidParameterException
	 */
	private static function validateGroupScoreboardAndContest(Request $r) {
		self::validateGroupScoreboard($r);
		
		Validators::isValidAlias($r["contest_alias"], "contest_alias");		
		try {
			$r["contest"] = ContestsDAO::getByAlias($r["contest_alias"]);
		} catch (Exception $ex) {
			throw new InvalidDatabaseOperationException($ex);
		}
		
		if (is_null($r["contest"])) {
			throw new InvalidParameterException("parameterNotFound", "Contest");
		}
		
		if ($r["contest"]->public == 0 && !Authorization::IsContestAdmin($r["current_user_id"], $r["contest"])) {
			throw new ForbiddenAccessException();
		}			
	}
	
	/**
	 * Add contest to a group scoreboard
	 * 
	 * @param Request $r
	 */
	public static function apiAddContest(Request $r) {				
		self::validateGroupScoreboardAndContest($r);				
		
		try {
			$groupScoreboardContest = new GroupsScoreboardsContests(array(
				"group_scoreboard_id" => $r["scoreboard"]->group_scoreboard_id,
				"contest_id" => $r["contest"]->contest_id
			));
			
			GroupsScoreboardsContestsDAO::save($groupScoreboardContest);
			
			self::$log->info("Contest " . $r["contest_alias"] . "added to scoreboard " . $r["scoreboard_alias"]);
		} catch (Exception $ex) {
			throw new InvalidDatabaseOperationException($ex);
		}
		
		return array("status" => "ok");
	}
	
	/**
	 * Add contest to a group scoreboard
	 * 
	 * @param Request $r
	 */
	public static function apiRemoveContest(Request $r) {				
		self::validateGroupScoreboardAndContest($r);
		
		try {
			$groupScoreboardContestKey = new GroupsScoreboardsContests(array(
				"group_scoreboard_id" => $r["scoreboard"]->group_scoreboard_id,
				"contest_id" => $r["contest"]->contest_id
			));
			
			$gscs = GroupsScoreboardsContestsDAO::search($groupScoreboardContestKey);
			if (is_null($gscs) || count($gscs) === 0) {
				throw new InvalidParameterException("parameterNotFound", "Contest");
			}
			
			GroupsScoreboardsContestsDAO::delete($groupScoreboardContestKey);
			
			self::$log->info("Contest " . $r["contest_alias"] . "removed from group " . $r["group_alias"]);
		} catch (ApiException $ex) {
			throw $ex;
		} catch (Exception $ex) {
			throw new InvalidDatabaseOperationException($ex);
		}
		
		return array("status" => "ok");
	}
	
	/**
	 * Details of a scoreboard. Returns a list with all contests that belong to
	 * the given scoreboard_alias
	 * 
	 * @param Request $r
	 */
	public static function apiDetails(Request $r) {
		self::validateGroupScoreboard($r);
		
		$response = array();
		
		// Fill contests
		$response["contests"] = array();
		$response["ranking"] = array();
		try {
			$groupScoreboardContestKey = new GroupsScoreboardsContests(array(
				"group_scoreboard_id" => $r["scoreboard"]->group_scoreboard_id,
			));
			
			$gscs = GroupsScoreboardsContestsDAO::search($groupScoreboardContestKey);			
			foreach($gscs as $gsc) {
				$contest = ContestsDAO::getByPK($gsc->contest_id);
				$response["contests"][] = $contest->asArray();
			}
			
		} catch (ApiException $ex) {
			throw $ex;
		} catch (Exception $ex) {
			throw new InvalidDatabaseOperationException($ex);
		}
		
		// Fill details of this scoreboard
		$response["scoreboard"] = $r["scoreboard"]->asArray();
		
		// If we have contests, calculate merged&filtered scoreboard
		if (count($response["contests"]) > 0) {
			// Get merged scoreboard
			$r["contest_aliases"] = array();
			foreach ($response["contests"] as $contest) {
				$r["contest_aliases"][] = $contest["alias"];
			}		

			$r["contest_aliases"] = implode(",", $r["contest_aliases"]);

			try {
				$groupUsers = GroupsUsersDAO::search(new GroupsUsers(array(
					"group_id" => $r["scoreboard"]->group_id
				)));		

				$r["usernames_filter"] = array();
				foreach ($groupUsers as $groupUser) {
					$user = UsersDAO::getByPK($groupUser->user_id);
					$r["usernames_filter"][] = $user->username;
				}

			} catch (Exception $ex) {
				throw new InvalidDatabaseOperationException($ex);
			}		

			$r["usernames_filter"] = implode(",", $r["usernames_filter"]);
			$mergedScoreboardResponse = ContestController::apiScoreboardMerge($r);

			$response["ranking"] = $mergedScoreboardResponse["ranking"];	
		}
		
		$response["status"] = "ok";		
		return $response;
	}
	
	/**
	 * Details of a scoreboard
	 * 
	 * @param Request $r
	 */
	public static function apiList(Request $r) {
		GroupController::validateGroup($r);
		
		$response = array();
		$response["scoreboards"] = array();
		try {
			$key = new GroupsScoreboards(array(
				"group_id" => $r["group"]->group_id
			));
			
			$scoreboards = GroupsScoreboardsDAO::search($key);
			foreach($scoreboards as $scoreboard) {
				$response["scoreboards"][] = $scoreboard->asArray();
			}
			
		} catch (Exception $ex) {
			throw new InvalidDatabaseOperationException($ex);
		}				
		
		$response["status"] = "ok";
		return $response;
	}
	
}

