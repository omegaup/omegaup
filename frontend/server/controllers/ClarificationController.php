<?php

/**
 * Description of ClarificationController
 *
 * @author joemmanuel
 */
class ClarificationController extends Controller {
	private static $contest;
	private static $problem;
	private static $clarification;

	/**
	 * Validate the request of apiCreate
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 */
	private static function validateCreate(Request $r) {
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

	private static function validateDetails(Request $r) {
		Validators::isNumber($r["clarification_id"], "clarification_id");

		// Check that the clarification actually exists
		try {
			self::$clarification = ClarificationsDAO::getByPK($r["clarification_id"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null(self::$clarification)) {
			throw new NotFoundException();
		}

		// If the clarification is private, verify that our user is invited or is contest director               
		if (self::$clarification->getPublic() === '0') {
			if (!(Authorization::CanViewClarification($r["current_user_id"], self::$clarification))) {
				throw new ForbiddenAccessException();
			}
		}
	}

	public static function apiDetails(Request $r) {
		// Authenticate the user
		self::authenticateRequest($r);

		// Validate request
		self::validateDetails($r);

		// Create array of relevant columns
		$relevant_columns = array("message", "answer", "time", "problem_id", "contest_id");

		// Add the clarificatoin the response
		$response = self::$clarification->asFilteredArray($relevant_columns);
		$response["status"] = "ok";

		return $response;
	}

	/**
	 * Validate update API request
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws ForbiddenAccessException
	 */
	private static function validateUpdate(Request $r) {
		Validators::isNumber($r["clarification_id"], "clarification_id");
		Validators::isStringNonEmpty($r["answer"], "answer", false /* not required */);
		Validators::isInEnum($r["public"], "public", array('0', '1'), false /* not required */);
		Validators::isStringNonEmpty($r["message"], "message", false /* not required */);

		// Check that clarification exists
		try {
			$r["clarification"] = ClarificationsDAO::getByPK($r["clarification_id"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (!Authorization::CanEditClarification($r["current_user_id"], $r["clarification"])) {
			throw new ForbiddenAccessException();
		}
	}

	/**
	 * Update a clarification
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiUpdate(Request $r) {
		// Authenticate user
		self::authenticateRequest($r);

		// Validate request 
		self::validateUpdate($r);

		// Update clarification        		
		if (!is_null($r["message"])) {
			$r["clarification"]->setMessage($r["message"]);
		}
		if (!is_null($r["answer"])) {
			$r["clarification"]->setAnswer($r["answer"]);
		}
		if (!is_null($r["clarification"])) {
			$r["clarification"]->setPublic($r["public"]);
		}

		// Let DB handle time update
		$r["clarification"]->setTime(NULL);

		// Save the clarification
		try {
			ClarificationsDAO::save($r["clarification"]);
		} catch (Exception $e) {
			// Operation failed in the data layer
			throw new InvalidDatabaseOperationException($e);
		}

		$response = array();
		$response["status"] = "ok";

		return $response;
	}
}
