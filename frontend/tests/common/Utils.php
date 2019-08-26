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

    public static function GetDbDatetime() {
        // Go to the DB

        return \OmegaUp\MySQLConnection::getInstance()->GetOne('SELECT NOW();');
    }

    public static function GetTimeFromUnixTimestamp($time) {
        // Go to the DB to take the unix timestamp

        return \OmegaUp\MySQLConnection::getInstance()->GetOne('SELECT FROM_UNIXTIME(?);', [$time]);
    }

    public static function CleanLog() {
        file_put_contents(OMEGAUP_LOG_FILE, '');
        file_put_contents(__DIR__ . '/../controllers/gitserver.log', '');
    }

    public static function CleanPath($path) {
        \OmegaUp\FileHandler::deleteDirRecursively($path);
        mkdir($path, 0755, true);
    }

    public static function deleteAllSuggestions() {
        \OmegaUp\MySQLConnection::getInstance()->Execute("DELETE FROM `QualityNominations` WHERE `nomination` = 'suggestion';");
    }

    public static function deleteAllRanks() {
        \OmegaUp\MySQLConnection::getInstance()->Execute('DELETE FROM `User_Rank`;');
    }

    public static function deleteAllPreviousRuns() {
        \OmegaUp\MySQLConnection::getInstance()->Execute('DELETE FROM `Submission_Log`;');
        \OmegaUp\MySQLConnection::getInstance()->Execute('UPDATE `Submissions` SET `current_run_id` = NULL;');
        \OmegaUp\MySQLConnection::getInstance()->Execute('DELETE FROM `Runs`;');
        \OmegaUp\MySQLConnection::getInstance()->Execute('DELETE FROM `Submissions`;');
    }

    public static function deleteAllProblemsOfTheWeek() {
        \OmegaUp\MySQLConnection::getInstance()->Execute('DELETE FROM `Problem_Of_The_Week`;');
    }

    /**
     * Given a run guid, set a score for its run
     *
     * @param ?int    $runID       The ID of the run.
     * @param ?string $runGuid     The GUID of the submission.
     * @param float   $points      The score of the run
     * @param string  $verdict     The verdict of the run.
     * @param ?int    $submitDelay The number of minutes worth of penalty.
     */
    public static function gradeRun(
        ?int $runId = null,
        ?string $runGuid,
        float $points = 1,
        string $verdict = 'AC',
        ?int $submitDelay = null
    ) : void {
        if (!is_null($runId)) {
            $run = RunsDAO::getByPK($runId);
            $submission = SubmissionsDAO::getByPK($run->submission_id);
        } else {
            $submission = SubmissionsDAO::getByGuid($runGuid);
            $run = RunsDAO::getByPK($submission->current_run_id);
        }

        $run->verdict = $verdict;
        $run->score = $points;
        $run->contest_score = $points * 100;
        $run->status = 'ready';
        $run->judged_by = 'J1';

        if (!is_null($submitDelay)) {
            $submission->submit_delay = $submitDelay;
            SubmissionsDAO::update($submission);
            $run->submit_delay = $submitDelay;
            $run->penalty = $submitDelay;
        }

        RunsDAO::update($run);

        Grader::getInstance()->setGraderResourceForTesting(
            $run,
            'details.json',
            json_encode([
                'verdict' => $verdict,
                'contest_score' => $points,
                'score' => $points,
                'judged_by' => 'RunsFactory.php',
            ])
        );
        // An empty gzip file.
        Grader::getInstance()->setGraderResourceForTesting(
            $run,
            'logs.txt.gz',
            "\x1f\x8b\x08\x08\xaa\x31\x34\x5c\x00\x03\x66\x6f" .
            "\x6f\x00\x03\x00\x00\x00\x00\x00\x00\x00\x00\x00"
        );
        // An empty zip file.
        Grader::getInstance()->setGraderResourceForTesting(
            $run,
            'files.zip',
            "\x50\x4b\x05\x06\x00\x00\x00\x00\x00\x00\x00\x00" .
            "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"
        );
    }

    public static function setUpDefaultDataConfig() {
        // Create a test default user for manual UI operations
        UserController::$sendEmailOnVerify = false;
        $admin = UserFactory::createUser(new UserParams([
            'username' => 'admintest',
            'password' => 'testtesttest',
        ]));
        ACLsDAO::create(new \OmegaUp\DAO\VO\ACLs([
            'acl_id' => Authorization::SYSTEM_ACL,
            'owner_id' => $admin->user_id,
        ]));
        UserRolesDAO::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $admin->user_id,
            'role_id' => Authorization::ADMIN_ROLE,
            'acl_id' => Authorization::SYSTEM_ACL,
        ]));
        UserFactory::createUser(new UserParams([
            'username' => 'test',
            'password' => 'testtesttest',
        ]));
        UserController::$sendEmailOnVerify = true;

        // Globally disable run wait gap.
        RunController::$defaultSubmissionGap = 0;
    }

    public static function CleanupFilesAndDb() {
        // Clean previous log
        self::CleanLog();
        // Clean problems and runs path
        self::CleanPath(IMAGES_PATH);
        self::CleanPath(RUNS_PATH);
        self::CleanPath(TEMPLATES_PATH);
        self::CleanPath(PROBLEMS_GIT_PATH);
        for ($i = 0; $i < 256; $i++) {
            mkdir(RUNS_PATH . sprintf('/%02x', $i), 0775, true);
        }
        // Clean DB
        self::CleanupDB();
    }

    public static function CleanupDB() {
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
            'Groups_Identities',
            'Groups_Scoreboards',
            'Groups_Scoreboards_Problemsets',
            'Identities',
            'Identity_Login_Log',
            'Interviews',
            'Notifications',
            'PrivacyStatement_Consent_Log',
            'Problems',
            'Problems_Forfeited',
            'Problems_Languages',
            'Problems_Tags',
            'Problemset_Access_Log',
            'Problemset_Problem_Opened',
            'Problemset_Problems',
            'Problemset_Identity_Request',
            'Problemset_Identity_Request_History',
            'Problemset_Identities',
            'Problemsets',
            'QualityNomination_Comments',
            'QualityNomination_Log',
            'QualityNomination_Reviewers',
            'QualityNominations',
            'Runs',
            'Schools',
            'Submissions',
            'Submission_Log',
            'Tags',
            'User_Roles',
            'Users',
            'Users_Badges',
            'Users_Experiments',
        ];

        try {
            // Disable foreign checks
            \OmegaUp\MySQLConnection::getInstance()->Execute('SET foreign_key_checks = 0;');

            foreach ($tables as $t) {
                \OmegaUp\MySQLConnection::getInstance()->Execute("TRUNCATE TABLE `$t`;");
            }

            // Tables with special entries.
            \OmegaUp\MySQLConnection::getInstance()->Execute('DELETE FROM `Groups` WHERE `alias` NOT LIKE "%:%";');

            // The format of the question changed from this id
            \OmegaUp\MySQLConnection::getInstance()->Execute('ALTER TABLE QualityNominations auto_increment = 18664');

            // Make sure the user_id and identity_id never matches in tests.
            \OmegaUp\MySQLConnection::getInstance()->Execute('ALTER TABLE Identities auto_increment = 100000;');
            self::setUpDefaultDataConfig();
        } catch (Exception $e) {
            echo 'Cleanup DB error. Tests will continue anyways:';
            var_dump($e->getMessage());
        } finally {
            // Enabling them again
            \OmegaUp\MySQLConnection::getInstance()->Execute('SET foreign_key_checks = 1;');
        }
        self::commit();
    }

    public static function RunUpdateUserRank() {
        // Ensure all suggestions are written to the database before invoking
        // the external script.
        self::commit();

        shell_exec('python3 ' . escapeshellarg(OMEGAUP_ROOT) . '/../stuff/cron/update_user_rank.py' .
        ' --quiet ' .
        ' --host ' . escapeshellarg(OMEGAUP_DB_HOST) .
        ' --user ' . escapeshellarg(OMEGAUP_DB_USER) .
        ' --database ' . escapeshellarg(OMEGAUP_DB_NAME) .
        ' --password ' . escapeshellarg(OMEGAUP_DB_PASS));
    }

    public static function Commit() {
        try {
            \OmegaUp\MySQLConnection::getInstance()->StartTrans();
        } finally {
            \OmegaUp\MySQLConnection::getInstance()->CompleteTrans();
        }
    }

    public static function RunAggregateFeedback() {
        // Ensure all suggestions are written to the database before invoking
        // the external script.
        self::commit();

        shell_exec('python3 ' . escapeshellarg(OMEGAUP_ROOT) . '/../stuff/cron/aggregate_feedback.py' .
                 ' --quiet ' .
                 ' --host ' . escapeshellarg(OMEGAUP_DB_HOST) .
                 ' --user ' . escapeshellarg(OMEGAUP_DB_USER) .
                 ' --database ' . escapeshellarg(OMEGAUP_DB_NAME) .
                 ' --password ' . escapeshellarg(OMEGAUP_DB_PASS));
    }

    public static function RunAssignBadges() {
        // Ensure everything is commited before invoking external script
        self::commit();
        shell_exec('python3 ' . escapeshellarg(OMEGAUP_ROOT) . '/../stuff/cron/assign_badges.py' .
                 ' --quiet ' .
                 ' --host ' . escapeshellarg(OMEGAUP_DB_HOST) .
                 ' --user ' . escapeshellarg(OMEGAUP_DB_USER) .
                 ' --database ' . escapeshellarg(OMEGAUP_DB_NAME) .
                 ' --password ' . escapeshellarg(OMEGAUP_DB_PASS));
    }
}
