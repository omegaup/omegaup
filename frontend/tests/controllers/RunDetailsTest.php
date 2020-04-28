<?php

/**
 * Description of RunDetailsTest
 *
 * @author juan.pablo
 */

class RunDetailsTest extends \OmegaUp\Test\ControllerTestCase {
    private function assertCanSeeRunDetails(
        string $guid,
        \OmegaUp\DAO\VO\Identities $user
    ) {
        $login = self::login($user);

        $response = \OmegaUp\Controllers\Run::apiDetails(
            new \OmegaUp\Request([
                'run_alias' => $guid,
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertArrayHasKey('details', $response);
    }

    private function assertCanNotSeeRunDetails(
        string $guid,
        \OmegaUp\DAO\VO\Identities $user
    ) {
        $login = self::login($user);

        $response = \OmegaUp\Controllers\Run::apiDetails(
            new \OmegaUp\Request([
                'run_alias' => $guid,
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertArrayNotHasKey('details', $response);
    }

    /**
     * A PHPUnit data provider for all the tests that can accept feedback.
     *
     * @return list<list<string>>
     */
    public function contestFeedbackProvider(): array {
        return [
            ['none'],
            ['summary'],
            ['detailed'],
        ];
    }

    /**
     * Admin should be able to see run details for all submissions, no matter
     * what is the verdict. Contestant only can see details when feedback field
     * in contest has not been set as none or verdict gotten is AC
     *
     * @dataProvider contestFeedbackProvider
     */
    public function testGetRunDetailsInContest(string $feedback) {
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'feedback' => $feedback,
            ])
        );

        $admin = $contestData['director'];
        $adminLogin = self::login($admin);

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            /*$params=*/null,
            $adminLogin
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        // Create contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $submissions = [];
        $waRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($waRunData, 0, 'WA', 60);

        $submissions[] = ['outContest', $waRunData['response']['guid']];
        // Admin always can see run details for all submissions
        $this->assertCanSeeRunDetails($waRunData['response']['guid'], $admin);
        // Contestant can not see run details when verdict is different to AC
        // or the submission is out of a contest
        $this->assertCanNotSeeRunDetails(
            $waRunData['response']['guid'],
            $identity
        );

        $paRunData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($paRunData, 0.5, 'PA', 55);

        $submissions[] = ['inContest', $paRunData['response']['guid']];

        // Admin always can see run details for all submissions, even if it is
        // in a contest
        $this->assertCanSeeRunDetails($paRunData['response']['guid'], $admin);
        if ($feedback === 'none') {
            // Whether contest feedback is set as 'none', user can not see run
            // details
            $this->assertCanNotSeeRunDetails(
                $paRunData['response']['guid'],
                $identity
            );
        } else {
            // When contest feedback is set as 'detailed' or 'summary' user can
            // see run details
            $this->assertCanSeeRunDetails(
                $paRunData['response']['guid'],
                $identity
            );
        }

        $login = self::login($identity);
        // Once again, user tries to solve the problem out of contest context
        $acRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($acRunData, 1, 'AC', 65);

        $submissions[] = ['outContest', $acRunData['response']['guid']];

        foreach ($submissions as $runGUID) {
            // Even though problem has been solved by user, details still
            // remain hidden for contest feedback 'none' until contest finishes
            $this->assertCanSeeRunDetails($runGUID[1], $admin);
            if ($feedback === 'none' && $runGUID[0] !== 'outContest') {
                $this->assertCanNotSeeRunDetails($runGUID[1], $identity);
            } else {
                $this->assertCanSeeRunDetails($runGUID[1], $identity);
            }
        }

        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60 * 60 * 2);

        foreach ($submissions as $runGUID) {
            // Now that contest has finished, both contestant and admin can see
            // run details for every submission related to the problem
            $this->assertCanSeeRunDetails($runGUID[1], $admin);
            $this->assertCanSeeRunDetails($runGUID[1], $identity);
        }
    }
}
