<?php
/**
 * Tests for Plagiarism Controller
 */

class PlagiarismTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCheckPlagiarismsScript() {
        $originalTime = \OmegaUp\Time::get();
        \OmegaUp\Time::setTimeForTesting($originalTime - (60 * 20));
        // Create a Contest.
        $contestData1 = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 10,
                'checkPlagiarism' => true,
            ])
        );
        $contestData2 = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 10,
                'checkPlagiarism' => false,
            ])
        );
        $contestData3 = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 39,
                'checkPlagiarism' => true,
            ])
        );
        // Get problems and add them to the contest
        $problems = [];
        foreach (range(0, 2) as $index) {
            $problems[$index] = \OmegaUp\Test\Factories\Problem::createProblem();

            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$index],
                $contestData1
            );
        }

        // Create our contestant
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $users = [];
        $users[0] = $identity1;
        $users[1] = $identity2;
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData1,
            $identity1
        );
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData1,
            $identity2
        );

        // Create request
        $login1 = self::login($identity1);
        $login2 = self::login($identity2);

        new \OmegaUp\Request([
            'contest_alias' => $contestData1['request']['alias'],
            'auth_token' => $login1->auth_token,
        ]);
        new \OmegaUp\Request([
            'contest_alias' => $contestData1['request']['alias'],
            'auth_token' => $login2->auth_token,
        ]);

        // Create one run for every problem
        $runs = [];
        foreach ($users as $_ => $identity) {
            foreach ($problems as $index => $problem) {
                \OmegaUp\Test\Factories\Contest::openProblemInContest(
                    $contestData1,
                    $problem,
                    $identity
                );
                $runs[$index] = \OmegaUp\Test\Factories\Run::createRun(
                    $problem,
                    $contestData1,
                    $identity
                );
                // Grade the run
                \OmegaUp\Test\Factories\Run::gradeRun($runs[$index]);
            }
        }
        /** @var list<array{alias: string}> */
        $contests = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            'SELECT alias FROM Contests as c
            WHERE c.`check_plagiarism` = 1 AND
            c.`finish_time` > NOW() - INTERVAL 20 MINUTE AND
            c.`finish_time` < NOW() AND
            c.`contest_id` NOT IN
                (SELECT p.`contest_id`
                FROM `Plagiarisms` as p)'
        );
        foreach ($contests as $contest) {
            $this-> assertFalse(
                in_array(
                    $contestData2['request']['alias'],
                    $contest
                )
            );
            $this-> assertFalse(
                in_array(
                    $contestData3['request']['alias'],
                    $contest
                )
            );
            $this-> assertTrue(
                in_array(
                    $contestData1['request']['alias'],
                    $contest
                )
            );
        }
        \OmegaUp\Time::setTimeForTesting($originalTime - (60 * 9));
        $test_path =  dirname(__DIR__, 3) . '/stuff/cron/testing/testdata';
        \OmegaUp\Test\Utils::runCheckPlagiarisms(
            $test_path
        );
        /** @var int|null */
        $this->assertEquals(
            3,
            \OmegaUp\MySQLConnection::getInstance()->GetOne(
                'SELECT COUNT(*) FROM Plagiarisms'
            )
        );
        // TODO: Add tests to assert submissions id are matching correctly

        \Omegaup\MySQLConnection::getInstance()->Execute(
            'DELETE FROM Plagiarisms'
        );
        \Omegaup\MySQLConnection::getInstance()->Execute('COMMIT');
    }
}
