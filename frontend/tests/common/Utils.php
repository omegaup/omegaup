<?php

/**
 * Test utils
 *
 * @author joemmanuel
 */
class Utils {
    public static $inittime;
    public static $counttime;

    //put your code here
    public static function cleanup() {
        foreach ($_REQUEST as $p) {
            unset($p);
        }
    }

    public static function CreateRandomString() {
        return md5(uniqid(rand(), true));
    }

    public static function GetValidPublicContestId() {
        // Create a clean contest and get the ID
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);

        return $contest_id;
    }

    public static function GetValidProblemOfContest($contest_id) {
        // Create problem in our contest
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);

        return $problem_id;
    }

    public static function DeleteAllContests() {
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

    public static function DeleteClarificationsFromProblem($problem_id) {
        self::ConnectToDB();

        // Get clarifications
        $clarifications = ClarificationsDAO::getAll();

        // Delete those who belong to problem_id
        foreach ($clarifications as $c) {
            if ($c->problem_id == $problem_id) {
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

    public static function GetPhpUnixTimestamp($time = null) {
        if (is_null($time)) {
            return time();
        } else {
            return strtotime($time);
        }
    }

    public static function GetDbDatetime() {
        // Go to the DB
        global $conn;

        $sql = 'SELECT NOW() n';
        $rs = $conn->GetRow($sql);

        if (count($rs) === 0) {
            return null;
        }

        return $rs['n'];
    }

    public static function GetTimeFromUnixTimestamp($time) {
        // Go to the DB to take the unix timestamp
        global $conn;

        $sql = 'SELECT FROM_UNIXTIME(?) t';
        $params = array($time);
        $rs = $conn->GetRow($sql, $params);

        if (count($rs) === 0) {
            return null;
        }

        return $rs['t'];
    }

    public static function getNextTime() {
        self::$counttime++;
        return Utils::GetTimeFromUnixTimestamp(self::$inittime + self::$counttime);
    }

    public static function CleanLog() {
        file_put_contents(OMEGAUP_LOG_FILE, '');
    }

    public static function CleanPath($path) {
        FileHandler::DeleteDirRecursive($path);
        mkdir($path, 0755, true);
    }

    public static function CleanupDB() {
        global $conn;

        // Tables to truncate
        $tables = array(
            'Auth_Tokens',
            'Clarifications',
            'Coder_Of_The_Month',
            'Contest_Access_Log',
            'Contest_Problem_Opened',
            'Contest_Problems',
            'Contest_User_Request',
            'Contest_User_Request_History',
            'Contests',
            'Contests_Users',
            'Emails',
            'Group_Roles',
            'Groups',
            'Groups_Scoreboards',
            'Groups_Scoreboards_Contests',
            'Groups_Users',
            'Interviews',
            'Problems',
            'Runs',
            'Submission_Log',
            'User_Login_Log',
            'User_Roles',
            'Users',
        );

        try {
            // Disable foreign checks
            $conn->Execute('SET foreign_key_checks = 0;');

            foreach ($tables as $t) {
                $sql = 'TRUNCATE TABLE `' . $t . '`; ';
                $conn->Execute($sql);
            }

            // Enabling them again
            $conn->Execute('SET foreign_key_checks = 1;');
        } catch (Exception $e) {
            echo 'Cleanup DB error. Tests will continue anyways:';
            var_dump($sql);
            var_dump($e->getMessage());

            $conn->Execute('SET foreign_key_checks = 1;');
        }
    }
}

/**
 * Test helper to enable/disable boolean flags on scope
 *
 */
class AutoEnableFlag {
    private $flag;

    function __construct(&$flag) {
        $this->flag = &$flag;
        $this->flag = true;
    }

    function __destruct() {
        $this->flag = false;
    }
}
