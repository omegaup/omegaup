<?php

/**
 * A contest might require registration to participate on it.
 *
 * @author alanboy@omegaup.com
 */
class RegisterToContestTest extends OmegaupTestCase {
	//pruebas (público, privado) x (usuario mortal, admin, invitado)
	//pruebas extra para distinguir entre invitado y ya entrado al concurso

	public function testSimpleRegistrationActions() {
		self::log("Started");
		//create a contest and its admin
		$contestData = ContestsFactory::createContest(null, 1 /*public*/);
		$contestAdmin = UserFactory::createUser();
		ContestsFactory::addAdminUser($contestData, $contestAdmin);

		//make it "registrable"
		self::log("Udate contest to make it registrable");
		$r1 = new Request();
		$r1["contest_alias"] = $contestData["request"]["alias"];
		$r1["contestant_must_register"] = true;
		$r1["auth_token"] = $this->login($contestAdmin);
		ContestController::apiUpdate($r1);

		//some user asks for contest
		$contestant = UserFactory::createUser();
		$r2 = new Request();
		$r2["contest_alias"] = $contestData["request"]["alias"];
		$r2["auth_token"] = $this->login($contestant);
		try {
			$response = ContestController::apiDetails($r2);
			$this->AssertFalse(true, "User gained access to contest even though its registration needed.");
		} catch(ForbiddenAccessException $fae) {
			// Expected. Continue.
		}

		self::log("user registers, into contest");
		ContestController::apiRegisterForContest($r2);

		//admin lists registrations
		$r3 = new Request();
		$r3["contest_alias"] = $contestData["request"]["alias"];
		$r3["auth_token"] = $this->login($contestAdmin);
		$result = ContestController::apiRequests($r3);
		$this->assertEquals(sizeof($result["users"]), 1);

		self::log("amin rejects registration");
		$r3["username"] = $contestant->username;
		$r3["resolution"] = false;
		ContestController::apiArbitrateRequest($r3);

		//ask for details again, this should fail again
		$r2 = new Request();
		$r2["contest_alias"] = $contestData["request"]["alias"];
		$r2["auth_token"] = $this->login($contestant);
		try {
			$response = ContestController::apiDetails($r2);
			$this->AssertFalse(true);
		} catch(ForbiddenAccessException $fae) {
			// Expected. Continue.
		}

		//admin admits user
		$r3["username"] = $contestant->username;
		$r3["resolution"] = true;
		ContestController::apiArbitrateRequest($r3);

		//user can now submit to contest
		$r2 = new Request();
		$r2["contest_alias"] = $contestData["request"]["alias"];
		$r2["auth_token"] = $this->login($contestant);

		// Explicitly join contest
		ContestController::apiOpen($r2);

		ContestController::apiDetails($r2);
	}
}
