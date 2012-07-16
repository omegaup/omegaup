<?php
/**
 * Authorization.php - Contains static function calls that return true if a user is authorized to perform certain action.
 */
class Authorization {
	public static function IsContestAdmin($user_id, Contests $contest) {
		return ($contest->getDirectorId() == $user_id || $user_id == 3 || $user_id == 37);
	}
}
