<?php

/**
 *   ApiException
 * 
 *   Exception that works with arrays instead of plain strings 
 * 
 * 
 */
class ApiException extends Exception {

	protected $header;
	private $customMessage;

	/**
	 * Builds an api exception
	 * 
	 * @param string $message
	 * @param string $header
	 * @param string $code
	 * @param Exception $previous
	 */
	function __construct($message, $header, $code, Exception $previous = NULL) {
		parent::__construct($message, $code, $previous);

		$this->header = $header;
		$this->customMessage = array();
	}

	/**
	 * Returns header
	 * 
	 * @return string
	 */
	public function getHeader() {
		return $this->header;
	}
	
	/**
	 * Adds a custom field to the asArray representation of this exception
	 * 
	 * @param string $key
	 * @param type $value
	 */
	public function addCustomMessageToArray($key, $value) {
		$this->customMessage[$key] = $value;
	}

	/**
	 * 
	 * @return array
	 */
	public function asArray() {
		$arrayToReturn =  array(
			"status" => "error",
			"error" => $this->message,
			"errorcode" => $this->code,
			"header" => $this->header,
			"cause" => !is_null($this->getPrevious()) ? $this->getPrevious()->getMessage() : NULL,
			"trace" => $this->getTraceAsString(),
		);
		
		return array_merge($arrayToReturn, $this->customMessage);
	}
	
	/**
	 * Returns exception info intended for public error msgs in http responses
	 * 
	 * @return array
	 */
	public function asResponseArray() {
		$arrayToReturn =  array(
			"status" => "error",
			"error" => $this->message,
			"errorcode" => $this->code,
			"header" => $this->header,			
		);
		
		return array_merge($arrayToReturn, $this->customMessage);
	}

}

/**
 * InvalidArgumentException
 * 
 */
class InvalidParameterException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct($message, Exception $previous = NULL) {
		parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
	}

}

/**
 * DuplicatedEntryInDatabaseException
 * 
 */
class DuplicatedEntryInDatabaseException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct($message, Exception $previous = NULL) {
		parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
	}

}

/**
 * DuplicatedEntryInDatabaseException
 * 
 */
class InvalidDatabaseOperationException extends ApiException {

	/**
	 *  
	 * @param Exception $previous
	 */
	function __construct(Exception $previous = NULL) {
		parent::__construct("Oops. I've encountered an internal error. Please try again.", 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
	}

}

/**
 * NotFoundException
 * 
 */
class NotFoundException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct($message, Exception $previous = NULL) {
		parent::__construct($message, 'HTTP/1.1 404 NOT FOUND', 404, $previous);
	}

}

/**
 * ForbiddenAccessException
 * 
 */
class ForbiddenAccessException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct($message = "User is not allowed to view this content.", Exception $previous = NULL) {
		parent::__construct($message, 'HTTP/1.1 403 FORBIDDEN', 403, $previous);
	}

}

/**
 * PreconditionFailed
 * 
 */
class PreconditionFailedException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct($message = "User is not allowed to view this content.", Exception $previous = NULL) {
		parent::__construct($message, 'HTTP/1.1 412 PRECONDITION FAILED', 412, $previous);
	}

}

/**
 * Filesystem operation failed
 * 
 */
class InvalidFilesystemOperationException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct($message = "Oops. I've encountered an internal error. Please try again.", Exception $previous = NULL) {
		parent::__construct($message, 'HTTP/1.1 500 INTERNAL SERVER ERROR', 500, $previous);
	}

}

/**
 * Default for unexpected errors
 * 
 */
class InternalServerErrorException extends ApiException {

	/**
	 * 
	 * @param Exception $previous
	 */
	function __construct(Exception $previous = NULL) {
		parent::__construct("Oops. I've encountered an internal error. Please try again.", 'HTTP/1.1 500 INTERNAL SERVER ERROR', 500, $previous);
	}

}

/**
 * Login failed exception
 * 
 */
class InvalidCredentialsException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct(Exception $previous = NULL) {
		parent::__construct("Username or password is wrong. Please check your credentials.", "HTTP/1.1 403 FORBIDDEN", 101, $previous);
	}

}

class NotAllowedToSubmitException extends ApiException {
	
	function __construct($message = "Unable to submit run: You're not allowed to submit yet. ", Exception $previous = NULL) {
		parent::__construct($message, "HTTP/1.1 401 FORBIDDEN", 501, $previous);
	}
}


class EmailNotVerifiedException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct(Exception $previous = NULL) {
		parent::__construct("Your email is not verified yet. Please check your e-mail.", "HTTP/1.1 403 FORBIDDEN", 600, $previous);
	}

}


class EmailVerificationSendException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct(Exception $previous = NULL) {
		parent::__construct("There was an error sending the verification email. Please try again later or contact joe@omegaup.com for support. Your email is not verified yet.", "HTTP/1.1 403 FORBIDDEN", 601, $previous);
	}

}

/**
 * ProblemDeploymentFailedException
 * 
 */
class ProblemDeploymentFailedException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct(Exception $previous = NULL) {
		parent::__construct("Unable to deploy problem, please check your zip: " . $previous->getMessage(), 'HTTP/1.1 412 PRECONDITION FAILED', 412, $previous);		
	}

}
