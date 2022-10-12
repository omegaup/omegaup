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
        );\OmegaUp\Time::setTimeForTesting($originalTime - (60 * 60));

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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identity
        );

        // Create request
        $login = self::login($identity);

        new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Create one run for every problem
        $runs = [];
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

        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 30,
                'check_plagiarism' => 1,
            ])
        );
        \OmegaUp\Test\Utils::runCheckPlagiarisms();
    }
}
