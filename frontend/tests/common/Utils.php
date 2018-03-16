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

    public static function GetPhpUnixTimestamp($time = null) {
        if (is_null($time)) {
            return Time::get();
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
        $params = [$time];
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
        $tables = [
            'ACLs',
            'Assignments',
            'Auth_Tokens',
            'Clarifications',
            'Coder_Of_The_Month',
            'Contest_Log',
            'Contests',
            'Courses',
            'Emails',
            'Group_Roles',
            'Groups_Scoreboards',
            'Groups_Scoreboards_Contests',
            'Groups_Users',
            'Identities',
            'Interviews',
            'Problems',
            'Problems_Languages',
            'Problems_Tags',
            'Problemset_Access_Log',
            'Problemset_Problem_Opened',
            'Problemset_Problems',
            'Problemset_User_Request',
            'Problemset_User_Request_History',
            'Problemset_Users',
            'Problemsets',
            'QualityNomination_Comments',
            'QualityNomination_Reviewers',
            'QualityNominations',
            'Runs',
            'Schools',
            'Submission_Log',
            'Tags',
            'User_Login_Log',
            'User_Roles',
            'Users',
            'Users_Experiments',
        ];

        try {
            // Disable foreign checks
            $conn->Execute('SET foreign_key_checks = 0;');

            foreach ($tables as $t) {
                $conn->Execute("TRUNCATE TABLE `$t`;");
            }

            // Tables with special entries.
            $conn->Execute('DELETE FROM `Groups` WHERE `alias` NOT LIKE "%:%";');

            // The format of the question changed from this id
            $conn->Execute('ALTER TABLE QualityNominations auto_increment = 18664');

            // Make sure the user_id and identity_id never matches in tests.
            $conn->Execute('ALTER TABLE Identities auto_increment = 100000;');
        } catch (Exception $e) {
            echo 'Cleanup DB error. Tests will continue anyways:';
            var_dump($e->getMessage());
        } finally {
            // Enabling them again
            $conn->Execute('SET foreign_key_checks = 1;');
        }
    }
}
