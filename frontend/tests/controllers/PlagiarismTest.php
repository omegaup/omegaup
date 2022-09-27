<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests for Plagiarism Controller
 */

class PlagiarismTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCheckPlagiarismsScript() {
        $originalTime = \OmegaUp\Time::get();

        $problemData1 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData2 = \OmegaUp\Test\Factories\Problem::createProblem();

        $this->$contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 45,
                'check_plagiarism' => 1,
            ])
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData1,
            $this->contest
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData2,
            $this->contest
        );
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 30,
                'check_plagiarism' => 1,
            ])
        );
        // add 2 problem to the contest. AddProblemToContest
        // create submission for the problem. 3 submissions of 3 different students. 
        // AddUser. Add 3 users to the contest. 

        // for each user , create one AC submission for each of the problems. 

        \OmegaUp\Test\Utils::runCheckPlagiarisms();
    }
}
