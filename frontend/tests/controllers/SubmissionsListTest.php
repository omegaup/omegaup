<?php

class SubmissionsListTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Create 4 users, 4 problems and grades each of them twice.
     *
     * - Page 1 should have 5 submissions since row count is given
     *
     * - Page 2 should have 3 submissions given the row count is given
     */
    public function testSubmissionsList() {
        $usersCount = 4;
        $numSubmissions = 2; // number of submissions for each problem
        foreach (range(0, $usersCount - 1) as $_) {
            [
                'identity' => $identity,
            ] = \OmegaUp\Test\Factories\User::createUser();
            $problem = \OmegaUp\Test\Factories\Problem::createProblem();

            foreach (range(0, $numSubmissions - 1) as $_) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problem,
                    $identity
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData);
            }
        }

        $submissions = \OmegaUp\Controllers\Submission::apiList(
            new \OmegaUp\Request([
                 'page' => 1,
                 'pageSize' => 5,
             ])
        )['submissions'];
         $this->assertCount(5, $submissions);
         // When visiting the second page, there should be 3 submissions left.
        $submissions = \OmegaUp\Controllers\Submission::apiList(
            new \OmegaUp\Request([
                 'page' => 2,
                 'pageSize' => 5,
             ])
        )['submissions'];
         $this->assertCount(3, $submissions);
    }

    /**
     * Create 2 users, 2 problems and grades each of them 6 times.
     *
     * - Page 1 should have 5 submissions since row count is given to be 5
     *
     * - Page 2 should have 1 submission given the row count is given.
     */
    public function testUserSubmissionsList() {
        $usersCount = 2;
        $numSubmissions = 6;
        $users = [];
        foreach (range(0, $usersCount - 1) as $i) {
            $users[] = \OmegaUp\Test\Factories\User::createUser();
            $problem = \OmegaUp\Test\Factories\Problem::createProblem();
            foreach (range(0, $numSubmissions - 1) as $_) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problem,
                    $users[$i]['identity']
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData);
            }
        }

        $submissions = \OmegaUp\Controllers\Submission::apiList(
            new \OmegaUp\Request([
                'page' => 1,
                'pageSize' => 5,
                'username' => $users[0]['identity']->username,
            ])
        )['submissions'];
        $this->assertCount(5, $submissions);

        // When visiting the second page, there should be 1 submissions left.
        $submissions = \OmegaUp\Controllers\Submission::apiList(
            new \OmegaUp\Request([
                'page' => 2,
                'pageSize' => 5,
                'username' => $users[0]['identity']->username,
            ])
        )['submissions'];
        $this->assertCount(1, $submissions);
    }

    public function testSubmissionPageSize() {
        $usersCount = 4;
        $pageSize =  501; //pageSize > MaxSubmissionPageSize i.e. 500
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
        try {
            \OmegaUp\Controllers\Submission::apiList(
                new \OmegaUp\Request([
                     'page' => 1,
                     'pageSize' => $pageSize,
                 ])
            )['submissions'];
            $this->fail('should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNumberTooLarge', $e->getMessage());
        }
    }
}
