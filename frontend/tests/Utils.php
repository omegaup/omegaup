<?php

namespace OmegaUp\Test;

/**
 * Test utils
 *
 * @author joemmanuel
 */
class Utils {
    public static function cleanup(): void {
        /** @var string $p */
        foreach ($_REQUEST as $p) {
            unset($_REQUEST[$p]);
        }
    }

    public static function createRandomString(): string {
        return md5(uniqid(strval(rand()), true));
    }

    private static function cleanPath(string $path): void {
        \OmegaUp\FileHandler::deleteDirRecursively($path);
        mkdir($path, 0755, true);
    }

    public static function deleteAllSuggestions(): void {
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            "DELETE FROM `QualityNominations` WHERE `nomination` = 'suggestion';"
        );
    }

    public static function deleteAllRanks(): void {
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'DELETE FROM `User_Rank`;'
        );
    }

    public static function deleteAllPreviousRuns(): void {
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'DELETE FROM `Submission_Log`;'
        );
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'UPDATE `Submissions` SET `current_run_id` = NULL;'
        );
        \OmegaUp\MySQLConnection::getInstance()->Execute('DELETE FROM `Runs`;');
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'DELETE FROM `Submissions`;'
        );
    }

    public static function deleteAllProblemsOfTheWeek(): void {
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'DELETE FROM `Problem_Of_The_Week`;'
        );
    }

    /**
     * Given a run guid, set a score for its run
     *
     * @param ?int    $runID            The ID of the run.
     * @param ?string $runGuid          The GUID of the submission.
     * @param float   $points           The score of the run
     * @param string  $verdict          The verdict of the run.
     * @param ?int    $submitDelay      The number of minutes worth of penalty.
     * @param int     $problemsetPoints The max score of the run for the problemset.
     */
    public static function gradeRun(
        ?int $runId = null,
        ?string $runGuid,
        float $points = 1,
        string $verdict = 'AC',
        ?int $submitDelay = null,
        int $problemsetPoints = 100
    ): void {
        if (!is_null($runId)) {
            $run = \OmegaUp\DAO\Runs::getByPK($runId);
            if (is_null($run) || is_null($run->submission_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException();
            }
            $submission = \OmegaUp\DAO\Submissions::getByPK(
                $run->submission_id
            );
            if (is_null($submission)) {
                throw new \OmegaUp\Exceptions\NotFoundException();
            }
        } else {
            if (is_null($runGuid)) {
                // At most one of $runId and $runGuid may be null.
                throw new \BadMethodCallException();
            }
            $submission = \OmegaUp\DAO\Submissions::getByGuid($runGuid);
            if (is_null($submission) || is_null($submission->current_run_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException();
            }
            $run = \OmegaUp\DAO\Runs::getByPK($submission->current_run_id);
            if (is_null($run)) {
                throw new \OmegaUp\Exceptions\NotFoundException();
            }
        }

        $run->verdict = $verdict;
        $run->score = $points;
        $run->contest_score = $points * $problemsetPoints;
        $run->status = 'ready';
        $run->judged_by = 'J1';

        if (!is_null($submitDelay)) {
            $submission->submit_delay = $submitDelay;
            \OmegaUp\DAO\Submissions::update($submission);
            $run->penalty = $submitDelay;
        }

        \OmegaUp\DAO\Runs::update($run);

        \OmegaUp\Grader::getInstance()->setGraderResourceForTesting(
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
        \OmegaUp\Grader::getInstance()->setGraderResourceForTesting(
            $run,
            'logs.txt.gz',
            "\x1f\x8b\x08\x08\xaa\x31\x34\x5c\x00\x03\x66\x6f" .
            "\x6f\x00\x03\x00\x00\x00\x00\x00\x00\x00\x00\x00"
        );
        // An empty zip file.
        \OmegaUp\Grader::getInstance()->setGraderResourceForTesting(
            $run,
            'files.zip',
            "\x50\x4b\x05\x06\x00\x00\x00\x00\x00\x00\x00\x00" .
            "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"
        );
    }

    public static function setUpDefaultDataConfig(): void {
        // Create a test default user for manual UI operations
        \OmegaUp\Controllers\User::$sendEmailOnVerify = false;
        ['user' => $admin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => 'admintest',
                'password' => 'testtesttest',
            ])
        );
        \OmegaUp\DAO\ACLs::create(new \OmegaUp\DAO\VO\ACLs([
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
            'owner_id' => $admin->user_id,
        ]));
        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $admin->user_id,
            'role_id' => \OmegaUp\Authorization::ADMIN_ROLE,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));
        \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams([
            'username' => 'test',
            'password' => 'testtesttest',
        ]));
        \OmegaUp\Controllers\User::$sendEmailOnVerify = true;

        // Globally disable run wait gap.
        \OmegaUp\Controllers\Run::$defaultSubmissionGap = 0;
    }

    public static function cleanupLogs(): void {
        file_put_contents(OMEGAUP_LOG_FILE, '');
        file_put_contents(OMEGAUP_MYSQL_TYPES_LOG_FILE, '');
        file_put_contents(__DIR__ . '/controllers/gitserver.log', '');
    }

    public static function cleanupFilesAndDB(): void {
        // Clean problems and runs path
        $runsPath = OMEGAUP_TEST_ROOT . 'submissions';
        // We need to have this directory be NOT within the /opt/omegaup directory
        // since we intend to share it through VirtualBox, and that does not support
        // mmapping files, which is needed for libgit2.
        $problemsGitPath = '/tmp/omegaup/problems.git';
        self::cleanPath(IMAGES_PATH);
        self::cleanPath($runsPath);
        self::cleanPath(TEMPLATES_PATH);
        self::cleanPath($problemsGitPath);
        for ($i = 0; $i < 256; $i++) {
            mkdir(sprintf('%s/%02x', $runsPath, $i), 0775, true);
        }
        // Clean DB
        self::CleanupDB();
    }

    public static function cleanupDB(): void {
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
            'Course_Identity_Request',
            'Course_Identity_Request_History',
            'Emails',
            'Group_Roles',
            'Groups_Identities',
            'Groups_Scoreboards',
            'Groups_Scoreboards_Problemsets',
            'Identities',
            'Identity_Login_Log',
            'Identities_Schools',
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
            'School_Of_The_Month',
            'Submissions',
            'Submission_Log',
            'Tags',
            'User_Roles',
            'User_Rank',
            'Users',
            'Users_Badges',
            'Users_Experiments',
        ];

        try {
            // Disable foreign checks
            \OmegaUp\MySQLConnection::getInstance()->Execute(
                'SET foreign_key_checks = 0;'
            );

            foreach ($tables as $t) {
                \OmegaUp\MySQLConnection::getInstance()->Execute(
                    "TRUNCATE TABLE `$t`;"
                );
            }

            // Tables with special entries.
            \OmegaUp\MySQLConnection::getInstance()->Execute(
                'DELETE FROM `Groups_` WHERE `alias` NOT LIKE "%:%";'
            );

            // The format of the question changed from this id
            \OmegaUp\MySQLConnection::getInstance()->Execute(
                'ALTER TABLE QualityNominations auto_increment = 18664'
            );

            // Make sure the user_id and identity_id never matches in tests.
            \OmegaUp\MySQLConnection::getInstance()->Execute(
                'ALTER TABLE Identities auto_increment = 100000;'
            );
            // Make sure the contest_id and problemset_id never matches in tests.
            \OmegaUp\MySQLConnection::getInstance()->Execute(
                'ALTER TABLE Contests auto_increment = 100000;'
            );
            self::setUpDefaultDataConfig();
        } catch (\Exception $e) {
            echo 'Cleanup DB error. Tests will continue anyways:';
            /** @psalm-suppress ForbiddenCode It's important to expose this error during tests. */
            var_dump($e->getMessage());
        } finally {
            // Enabling them again
            \OmegaUp\MySQLConnection::getInstance()->Execute(
                'SET foreign_key_checks = 1;'
            );
        }
        self::commit();
    }

    private static function shellExec(string $command): void {
        /** @psalm-suppress ForbiddenCode this only runs in tests. */
        $proc = proc_open(
            $command,
            [
                 0 => ['file', '/dev/null', 'r'],
                 1 => ['pipe', 'w'],
                 2 => ['pipe', 'w'],
            ],
            $pipes
        );
        if (!is_resource($proc)) {
            throw new \Exception("Failed to run `{$command}`");
        }
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $processStatus = proc_close($proc);
        if ($processStatus != 0) {
            throw new \Exception(
                "Failed to run `{$command}`: status={$processStatus}\nstdout={$stdout}\nstderr={$stderr}"
            );
        }
    }

    public static function commit(): void {
        try {
            \OmegaUp\MySQLConnection::getInstance()->StartTrans();
        } finally {
            \OmegaUp\MySQLConnection::getInstance()->CompleteTrans();
        }
    }

    public static function runUpdateRanks(
        string $runDate = null
    ): void {
        // Ensure all suggestions are written to the database before invoking
        // the external script.
        self::commit();
        $date = is_null(
            $runDate
        ) ? '' : (' --date ' . escapeshellarg(
            strval(
                $runDate
            )
        ));
        self::shellExec(
            ('python3 ' .
             escapeshellarg(strval(OMEGAUP_ROOT)) .
             '/../stuff/cron/update_ranks.py' .
             ' --verbose ' .
             ' --host ' . escapeshellarg(OMEGAUP_DB_HOST) .
             ' --user ' . escapeshellarg(OMEGAUP_DB_USER) .
             ' --database ' . escapeshellarg(OMEGAUP_DB_NAME) .
            ' --password ' . escapeshellarg(OMEGAUP_DB_PASS) .
            $date)
        );
    }

    public static function runAggregateFeedback(): void {
        // Ensure all suggestions are written to the database before invoking
        // the external script.
        self::commit();
        self::shellExec(
            ('python3 ' .
             escapeshellarg(strval(OMEGAUP_ROOT)) .
             '/../stuff/cron/aggregate_feedback.py' .
             ' --verbose ' .
             ' --host ' . escapeshellarg(OMEGAUP_DB_HOST) .
             ' --user ' . escapeshellarg(OMEGAUP_DB_USER) .
             ' --database ' . escapeshellarg(OMEGAUP_DB_NAME) .
             ' --password ' . escapeshellarg(OMEGAUP_DB_PASS))
        );
    }

    public static function runAssignBadges(): void {
        // Ensure everything is commited before invoking external script
        self::commit();
        self::shellExec(
            ('python3 ' .
             escapeshellarg(strval(OMEGAUP_ROOT)) .
             '/../stuff/cron/assign_badges.py' .
             ' --verbose ' .
             ' --host ' . escapeshellarg(OMEGAUP_DB_HOST) .
             ' --user ' . escapeshellarg(OMEGAUP_DB_USER) .
             ' --database ' . escapeshellarg(OMEGAUP_DB_NAME) .
             ' --password ' . escapeshellarg(OMEGAUP_DB_PASS))
        );
    }
}
