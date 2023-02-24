<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests for Plagiarism Controller
 */

class PlagiarismTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCheckPlagiarismsScript() {
        $originalTime = \OmegaUp\Time::get();
        // Create a Contest.
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 45,
                'check_plagiarism' => 1,
            ])
        );

        // Get problems and add them to the contest
        $problems = [];
        foreach (range(0, 2) as $index) {
            $problems[$index] = \OmegaUp\Test\Factories\Problem::createProblem();

            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$index],
                $contestData
            );
        }

        // Create our contestant
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $users = [];
        $users[0] = $identity1;
        $users[1] = $identity2;
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identity1
        );
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identity2
        );

        // Create request
        $login1 = self::login($identity1);
        $login2 = self::login($identity2);

        new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login1->auth_token,
        ]);
        new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login2->auth_token,
        ]);

        // Create one run for every problem
        $runs = [];
        foreach ($users as $id => $identity) {
            foreach ($problems as $index => $problem) {
                \OmegaUp\Test\Factories\Contest::openProblemInContest(
                    $contestData,
                    $problem,
                    $identity
                );

                $runs[$index] = \OmegaUp\Test\Factories\Run::createRun(
                    $problem,
                    $contestData,
                    $identity
                );

                // Grade the run
                \OmegaUp\Test\Factories\Run::gradeRun($runs[$index]);
            }
        }
        \OmegaUp\Time::setTimeForTesting($originalTime - (60 * 44));

        $local_downloader_dir = '/opt/omegaup/stuff/cron/testing/testdata';
        \OmegaUp\Test\Utils::runCheckPlagiarisms($local_downloader_dir);

        $sql1 = 'SELECT guid
                FROM
                    Submissions';
        $rs1 = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql1);
    }
}
