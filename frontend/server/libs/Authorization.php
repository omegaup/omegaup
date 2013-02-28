<?php

/**
 * Authorization.php - Contains static function calls that return true if a user is authorized to perform certain action.
 */
define('ADMIN_ROLE', '1');

class Authorization {

	public static function CanViewRun($user_id, Runs $run) {
		if (is_null($run) || !is_a($run, "Runs")) {
			return false;
		}

		return (
				$run->getUserId() === $user_id ||
				Authorization::CanEditRun($user_id, $run)
				);
	}

	public static function CanEditRun($user_id, Runs $run) {
		if (is_null($run) || !is_a($run, "Runs")) {
			return false;
		}

		try {
			$contest = ContestsDAO::getByPK($run->getContestId());
			$problem = ProblemsDAO::getByPK($run->getProblemId());
		} catch (Exception $e) {
			throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
		}

		if (is_null($problem)) {
			return false;
		}

		$isContestAdmin = false;
		if (!is_null($contest)) {
			$isContestAdmin = Authorization::IsContestAdmin($user_id, $contest);
		}

		return $isContestAdmin
				|| self::IsSystemAdmin($user_id)
				|| $problem->getAuthorId() === $user_id;
	}

	public static function CanViewClarification($user_id, Clarifications $clarification) {
		if (is_null($clarification) || !is_a($clarification, "Clarifications")) {
			return false;
		}

		try {
			$contest = ContestsDAO::getByPK($clarification->getContestId());
		} catch (Exception $e) {
			throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
		}

		if (is_null($contest)) {
			return false;
		}

		return ($clarification->getAuthorId() === $user_id
				|| Authorization::IsContestAdmin($user_id, $contest));
	}

	public static function CanEditClarification($user_id, Clarifications $clarification) {
		if (is_null($clarification) || !is_a($clarification, "Clarifications")) {
			return false;
		}

		try {
			$contest = ContestsDAO::getByPK($clarification->getContestId());
			$problem = ProblemsDAO::getByPK($clarification->getProblemId());
		} catch (Exception $e) {
			throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
		}

		if (is_null($contest) || is_null($problem)) {
			return false;
		}

		return ($problem->getAuthorId() === $user_id
				|| Authorization::IsContestAdmin($user_id, $contest));
	}

	public static function CanEditProblem($user_id, Problems $problem) {
		if (is_null($problem) || !is_a($problem, "Problems")) {
			return false;
		}

		return ($problem->getAuthorId() === $user_id || Authorization::IsSystemAdmin($user_id));
	}

	public static function IsContestAdmin($user_id, Contests $contest) {
		if (is_null($contest) || !is_a($contest, "Contests")) {
			return false;
		}

		return ($contest->getDirectorId() === $user_id) || self::IsSystemAdmin($user_id);
	}

	public static function IsSystemAdmin($user_id) {
		try {
			$ur = UserRolesDAO::getByPK($user_id, ADMIN_ROLE, NULL /* general admin */);

			return !is_null($ur);
		} catch (Exception $e) {			
			throw new InvalidDatabaseOperationException($e);
		}
	}

	// @todo user in contest
}
