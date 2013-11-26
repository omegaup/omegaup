<?php


/**
 * Test getting general user Info methods 
 *
 * @author Alberto
 */
class CoderOfTheMonthTest extends OmegaupTestCase {
	
	
	public function testCoderOfTheMonthCalc() {
		
		$user = UserFactory::createUser();
		
		$contest = ContestsFactory::createContest();
		$problem = ProblemsFactory::createProblem();
		
		ContestsFactory::addProblemToContest($problem, $contest);
		ContestsFactory::addUser($contest, $user);
		
		// Creating 10 AC runs for our user in the last month
		$n = 10;
		
		$lastMonth = intval(date('m')) - 1;
		$runCreationDate = date('Y-' . $lastMonth . '-01');
		
		for ($i = 0; $i < $n; $i++) {
			$runData = RunsFactory::createRun($problem, $contest, $user);
			RunsFactory::gradeRun($runData);
			
			// Force the run to be in last month
			$run = RunsDAO::getByAlias($runData["response"]["guid"]);
			$run->setTime($runCreationDate);
			RunsDAO::save($run);
		}
		
		$response = UserController::apiCoderOfTheMonth(new Request());
		
		$this->assertEquals($user->getUsername(), $response["userinfo"]["username"]);
	}	
	
	public function testCoderOfTheMonthList() {
		
		$user = UserFactory::createUser();
		$auth_token = $this->login($user);
		
		$r = new Request(array(
			"auth_token" => $auth_token
		));
		
		$response = UserController::apiCoderOfTheMonthList($r);
				
		$this->assertEquals(1, count($response["coders"]));		
	}
}
?>
