<?php

/**
 * Description of ContestRemoveUserTest
 *
 * @author joemmanuel
 */
class ContestRemoveUserTest extends OmegaupTestCase {
	public function testRemoveUser() {
		// Get a contest
		$contestData = ContestsFactory::createContest();

		// Create a user
		$user = UserFactory::createUser();

		// Add user to contest
		ContestsFactory::addUser($contestData, $user);

		// Validate 0 users
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["auth_token"] = $this->login($contestData["director"]);
		$response = ContestController::apiUsers($r);
		$this->assertEquals(1, count($response["users"]));

		// Remove user
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["usernameOrEmail"] = $user->getUsername();
		$r["auth_token"] = $this->login($contestData["director"]);
		ContestController::apiRemoveUser($r);

		// Validate 0 users in contest
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["auth_token"] = $this->login($contestData["director"]);
		$response = ContestController::apiUsers($r);
		$this->assertEquals(0, count($response["users"]));
	}
}

