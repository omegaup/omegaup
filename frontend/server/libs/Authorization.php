<?php

/**
 * Authorization.php - Contains static function calls that return true if a user is authorized to perform certain action.
 */
define('ADMIN_ROLE', '1');
define('CONTEST_ADMIN_ROLE', '2');
define('PROBLEM_ADMIN_ROLE', '3');

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
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($problem)) {
			return false;
		}

		if ($problem->deprecated) {
			throw new PreconditionFailedException('problemDeprecated');
		}

		$isContestAdmin = false;
		if (!is_null($contest)) {
			$isContestAdmin = Authorization::IsContestAdmin($user_id, $contest);
		}

		$isProblemAdmin = Authorization::IsProblemAdmin($user_id, $problem);

		return $isContestAdmin
			|| $isProblemAdmin;
	}

	public static function CanViewClarification($user_id, Clarifications $clarification) {
		if (is_null($clarification) || !is_a($clarification, "Clarifications")) {
			return false;
		}

		if ($clarification->author_id === $user_id) {
			return true;
		}

		try {
			$contest = ContestsDAO::getByPK($clarification->contest_id);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($contest)) {
			return false;
		}

		return Authorization::IsContestAdmin($user_id, $contest);
	}

	public static function CanEditClarification($user_id, Clarifications $clarification) {
		if (is_null($clarification) || !is_a($clarification, "Clarifications")) {
			return false;
		}

		try {
			$contest = ContestsDAO::getByPK($clarification->getContestId());
			$problem = ProblemsDAO::getByPK($clarification->getProblemId());
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
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

		return Authorization::IsProblemAdmin($user_id, $problem);
	}

	public static function IsContestAdmin($user_id, Contests $contest) {
		if (is_null($contest) || !is_a($contest, "Contests")) {
			return false;
		}

		try {
			$ur = UserRolesDAO::getByPK($user_id, CONTEST_ADMIN_ROLE, $contest->getContestId());
			if (!is_null($ur)) {
				return true;
			}
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		return ($contest->getDirectorId() === $user_id) || self::IsSystemAdmin($user_id);
	}

	public static function IsProblemAdmin($user_id, Problems $problem) {
		if (is_null($problem)) {
			return false;
		}

		try {
			$ur = UserRolesDAO::getByPK($user_id, PROBLEM_ADMIN_ROLE, $problem->problem_id);

			if (!is_null($ur)) {
				return true;
			}
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		return ($problem->author_id === $user_id) || self::IsSystemAdmin($user_id);
	}

	public static function IsSystemAdmin($user_id) {
		try {
			$ur = UserRolesDAO::getByPK($user_id, ADMIN_ROLE, 0 /* general admin */);

			return !is_null($ur);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
	}

	public static function IsGroupOwner($user_id, Groups $group) {
		if (is_null($group)) {
			return false;
		}

		if ($user_id === $group->owner_id) {
			return true;
		}

		return false;
	}
}
