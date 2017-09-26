<?php

/**
 * Description of ArenaAdminAllTest
 *
 * @author joemmanuel
 */
class ArenaAdminAllTestextends extends OmegaupUITestCase {
    public function testProblemArenaAndSubmbit() {
        // Create a contestant
        $contestant = UserFactory::createUser();

        // Create a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest([]);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Login
        $contestant = $this->createAdminUserAndLogin();

        // Open ADMIN
        $this->open('/arena/admin');

        // Wait for table to render with our run
        $this->waitForElementPresent('//*[@id="run_'.$runData['response']['guid'].'"]/td[2]');
    }
}
