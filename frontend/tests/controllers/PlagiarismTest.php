<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests for Plagiarism Controller
 */

class PlagiarismTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCheckPlagiarismsScript() {
        $originalTime = \OmegaUp\Time::get();
        \OmegaUp\Time::setTimeForTesting($originalTime - (60 * 10));
        // Create a Contest.
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 10,
                'checkPlagiarism' => true,
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
        \OmegaUp\Test\Utils::runCheckPlagiarisms(
            '/opt/omegaup/stuff/cron/testing/testdata/'
        );

        $this->assertEquals(
            3,
            \OmegaUp\MySQLConnection::getInstance()->GetOne(
                'SELECT COUNT(*) FROM Plagiarisms'
            )
        );
        $expected_result = [[2, 5], [1,4], [3,6]];
        $index = 0;
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            'SELECT * FROM Plagiarisms'
        );
        foreach ($result as $submission) {
            $this->assertEquals(
                'integer',
                gettype(
                    $submission['submission_id_1']
                )
            );
            $this->assertEquals(
                'integer',
                gettype(
                    $submission['submission_id_2']
                )
            );
            $this->assertEquals(
                $expected_result[$index][0],
                $submission['submission_id_1']
            );
            $this->assertEquals(
                $expected_result[$index][1],
                $submission['submission_id_2']
            );
            $this->assertEquals(100, $submission['score_1']);
            $this->assertEquals(100, $submission['scroe_2']);
            $index += 1;
        }
    }
}
