<?php

/**
 * Description of ClarificationController
 *
 * @author joemmanuel
 */
class ClarificationController extends Controller {

	private static $contest;
	private static $problem;

	/**
	 * Validate the request of apiCreate
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 */
	public static function validateCreate(Request $r) {

		Validators::isStringNonEmpty($r["contest_alias"], "contest_alias");
		Validators::isStringNonEmpty($r["problem_alias"], "problem_alias");
		Validators::isStringNonEmpty($r["message"], "message");

		try {
			self::$contest = ContestsDAO::getByAlias($r["contest_alias"]);
			self::$problem = ProblemsDAO::getByAlias($r["problem_alias"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null(self::$contest)) {
			throw new NotFoundException("Contest provided does not exists");
		}

		if (is_null(self::$problem)) {
			throw new NotFoundException("Problem provided does not exists");
		}

		// Is the combination contest_id and problem_id valid?        
		if (is_null(ContestProblemsDAO::getByPK(self::$contest->getContestId(), self::$problem->getProblemId()))) {
			throw new NotFoundException();
		}
	}

	/**
	 * Creates a Clarification
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiCreate(Request $r) {

		// Authenticate user
		self::authenticateRequest($r);

		// Validate request
		self::validateCreate($r);		

		$response = array();
		
		$clarification = new Clarifications(array(
					"author_id" => $r["current_user_id"],
					"contest_id" => self::$contest->getContestId(),
					"problem_id" => self::$problem->getProblemId(),
					"message" => $r["message"],
					"public" => '0'
				));

		// Insert new Clarification
		try {
			// Save the clarification object with data sent by user to the database
			ClarificationsDAO::save($clarification);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		$response["clarification_id"] = $clarification->getClarificationId();
		$response["status"] = "ok";
		
		return $response;
	}

}

