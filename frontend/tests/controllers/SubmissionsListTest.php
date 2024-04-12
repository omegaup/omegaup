<?php

class SubmissionsListTest extends \OmegaUp\Test\ControllerTestCase {
    public function testSubmissionsList() {
        /**
         * Create 8 submissions of different users to different problems.
         */
        $usersCount = 8;

        foreach (range(0, $usersCount - 1) as $_) {
            [
                'identity' => $identity,
            ] = \OmegaUp\Test\Factories\User::createUser();
            $problem = \OmegaUp\Test\Factories\Problem::createProblem();

            $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problem,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        $submissions = \OmegaUp\Controllers\Submission::apiList(
            new \OmegaUp\Request([
                'page' => 1,
                'rowcount' => 5,
            ])
        )['results'];
        $this->assertCount(5, $submissions);

        // When visiting the second page, there should be 3 submissions left.
        $submissions = \OmegaUp\Controllers\Submission::apiList(
            new \OmegaUp\Request([
                'page' => 2,
                'rowcount' => 5,
            ])
        )['results'];
        $this->assertCount(3, $submissions);
    }
}
