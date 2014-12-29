<?php

/**
 * Los admins/directores de los concursos deben
 * poder participar en los concursos que crean.
 *
 *
 */
class ContestAdminCanSubmitTest extends OmegaupTestCase {

	public function testContestAdminCanSubmit() {

		$contestDirector = UserFactory::createUser();

		$contestData = ContestsFactory::createContest(
											null /* title */,
											$public = 1,
											$contestDirector);

		$problemData = ProblemsFactory::createProblem();

		ContestsFactory::addProblemToContest($problemData, $contestData);

		$randomUser = UserFactory::createUser();

		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["auth_token"] = $this->login($randomUser);

		$response = ContestController::apiScoreboard($r);
		$this->assertEquals(0, count($response["ranking"]), "Ranking count should be zero");

		$runData = RunsFactory::createRun($problemData, $contestData, $contestDirector);

		$response = ContestController::apiScoreboard($r);
		$this->assertEquals(1, count($response["ranking"]), "Ranking should contain admin's run");

		

	}
}
