<?php

/**
 * Description of AddProblemToContestUITest
 *
 * @author joemmanuel
 */

require_once 'controllers/ContestAddProblemTest.php';

class AddProblemToContestUITest extends OmegaupUITestCase {
    public function testAddProblemToContest() {
        // Login
        $author = $this->createUserAndLogin();

        // Create a problem
        $problemData = ProblemsFactory::createProblem(null, null, 1, $author);

        // Create a contest
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();
        ContestsFactory::addAdminUser($contestData, $author);

        // Open page
        $this->open('/addproblemtocontest.php');

        // Wait for ajax to populate
        sleep(1);

        $this->type('name=problems', $problemData['request']['alias']);
        $this->type('name=contests', $contestData['request']['alias']);

        // Click Agregar problema
        $this->click("//input[@value='Agregar problema']");

        // Assert
        $this->waitForElementPresent('id=status');
        sleep(1);
        $this->assertElementContainsText('id=status', 'Problem successfully added!');

        // Check db
        AddProblemToContestTest::assertProblemAddedToContest($problemData, $contestData, ['points' => 100, 'order_in_contest' => 1]);
    }
}
