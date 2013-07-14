<?php

/**
 * Test utils
 *
 * @author joemmanuel
 */
class Utils {

	static $inittime;
	static $counttime;

	//put your code here
	static function cleanup() {
		foreach ($_REQUEST as $p) {
			unset($p);
		}
	}

	static function CreateRandomString() {
		return md5(uniqid(rand(), true));
	}

	static function GetValidPublicContestId() {
		// Create a clean contest and get the ID
		$contestCreator = new NewContestTest();
		$contest_id = $contestCreator->testCreateValidContest(1);

		return $contest_id;
	}

	static function GetValidProblemOfContest($contest_id) {
		// Create problem in our contest
		$problemCreator = new NewProblemInContestTest();
		$problem_id = $problemCreator->testCreateValidProblem($contest_id);

		return $problem_id;
	}

	static function DeleteAllContests() {
		try {
			$contests = ContestsDAO::getAll();
			foreach ($contests as $c) {
				ContestsDAO::delete($c);
			}
		} catch (ApiException $e) {
			// Propagate exception
			var_dump($e->getArrayMessage());
			throw $e;
		}
	}

	static function DeleteClarificationsFromProblem($problem_id) {
		self::ConnectToDB();

		// Get clarifications
		$clarifications = ClarificationsDAO::getAll();

		// Delete those who belong to problem_id
		foreach ($clarifications as $c) {
			if ($c->getProblemId() == $problem_id) {
				try {
					ClarificationsDAO::delete($c);
				} catch (ApiException $e) {
					var_dump($e->getArrayMessage());
					throw $e;
				}
			}
		}

		self::cleanup();
	}

	static function GetPhpUnixTimestamp($time = NULL) {
		if (is_null($time)) {
			return time();
		} else {
			return strtotime($time);
		}
	}

	static function GetDbDatetime() {
		// Go to the DB 
		global $conn;

		$sql = "SELECT NOW()";
		$rs = $conn->GetRow($sql);

		if (count($rs) === 0) {
			return NULL;
		}

		return $rs[0];
	}

	static function GetTimeFromUnixTimestam($time) {
		// Go to the DB to take the unix timestamp
		global $conn;

		$sql = "SELECT FROM_UNIXTIME(?)";
		$params = array($time);
		$rs = $conn->GetRow($sql, $params);

		if (count($rs) === 0) {
			return NULL;
		}

		return $rs[0];
	}

	static function getNextTime() {
		self::$counttime++;
		return Utils::GetTimeFromUnixTimestam(self::$inittime + self::$counttime);
	}

	static function CleanLog() {
		file_put_contents(OMEGAUP_LOG_ACCESS_FILE, "");
		file_put_contents(OMEGAUP_LOG_ERROR_FILE, "");
	}

	static function CleanPath($path) {
		self::DeleteDirRecursive($path);
		mkdir($path);
	}

	static function CleanupDB() {
		global $conn;

		// Tables to truncate
		$tables = array(
			'Runs',
			'Contest_Problems',
			'Contests_Users',
			'Clarifications',
			'Contest_Problem_Opened',
			'Problems',
			'Auth_Tokens',
			'Contests',
			'Emails',
			'User_Roles',
			'Users',			
		);

		try {
			// Disable foreign checks 
			$conn->Execute("SET foreign_key_checks = 0;");
			
			foreach ($tables as $t) {
				$sql = "TRUNCATE TABLE `" . $t . "`; ";
				$conn->Execute($sql);
			}
			
			// Enabling them again
			$conn->Execute("SET foreign_key_checks = 1;");
		} catch (Exception $e) {
			echo "Cleanup DB error. Tests will continue anyways:";
			var_dump($sql);
			var_dump($e->getMessage());
			
			$conn->Execute("SET foreign_key_checks = 1;");
		}
	}

	static function DeleteDirRecursive($pathName) {
		self::rrmdir($pathName);
	}

	private static function rrmdir($dir) {
		if (!file_exists($dir)) {
			return;
		}

		foreach (glob($dir . '/*') as $file) {
			if (is_dir($file)) {
				self::rrmdir($file);
			} else if (!unlink($file)) {
				throw new Exception("FATAL: Not able to delete file " . $file);
			}
		}

		if (!rmdir($dir)) {
			throw new Exception("FATAL: Not able to delete dir " . $dir);
		}
	}
}

?>
