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
                // TO:DO Use check plagiarism flag here
            ])
        );
        \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'startTime' => $originalTime - 60 * 60,
                'finishTime' => $originalTime - 60 * 30,
                // TO:DO Use check plagiarism flag here
            ])
        );
        \OmegaUp\Test\Utils::runCheckPlagiarisms();
    }
}