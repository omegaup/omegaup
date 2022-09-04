<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests for Plagiarism Controller
 */

class PlagiarismTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCheckPlagiarismsScript() {
        $originalTime = \OmegaUp\Time::get();
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 45,
                'check_plagiarism' => 1
            ])
        );
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 30,
                'check_plagiarism' => 1
            ])
        );
        \OmegaUp\Test\Utils::runCheckPlagiarisms();
    }
}
