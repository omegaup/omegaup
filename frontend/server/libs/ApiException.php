<?php

/**
 *   ApiException
 * 
 *   Exception that works with arrays instead of plain strings 
 * 
 * 
 */
abstract class ApiException extends Exception {
	public static $log;

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
			"error" => $this->getErrorMessage(),
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
			"error" => $this->getErrorMessage(),
			"errorcode" => $this->code,
			"header" => $this->header
		);
		
		return array_merge($arrayToReturn, $this->customMessage);
	}

	protected function getErrorMessage() {
		// obtener el texto final (ya localizado) de smarty.
		global $smarty;
		$localizedText = $smarty->getconfigvars($this->message);
		if (empty($localizedText)) {
			self::$log->error("Untranslated error message: {$this->message}");
			return "{untranslated:{$this->message}";
		} else {
			return $localizedText;
		}
	}
}

ApiException::$log = Logger::getLogger("ApiException");

/**
 * InvalidArgumentException
 * 
 */
class InvalidParameterException extends ApiException {

	private $parameter;
	private $additional_parameters;

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct($message, $parameter = NULL, $additional_parameters = array()) {
		parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400);
		$this->parameter = $parameter;
		$this->additional_parameters = $additional_parameters;
	}

	protected function getErrorMessage() {
		// Obtener el texto final (ya localizado) de smarty.
		global $smarty;
		$localizedText = $smarty->getConfigVars($this->message);
		if (empty($localizedText)) {
			self::$log->error("Untranslated error message: {$this->message}");
			return "{untranslated:{$this->message}";
		}
		foreach ($this->additional_parameters as $key => $value) {
			$localizedText = str_replace("%($key)", $value, $localizedText);
		}
		if ($this->parameter == NULL) {
			return $localizedText;
		} else {
			return "$localizedText: {$this->parameter}";
		}
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
		parent::__construct("generalError", 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
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
	function __construct($message = "userNotAllowed", Exception $previous = NULL) {
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
	function __construct($message = "userNotAllowed", Exception $previous = NULL) {
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
	function __construct($message = "generalError", Exception $previous = NULL) {
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
		parent::__construct("generalError", 'HTTP/1.1 500 INTERNAL SERVER ERROR', 500, $previous);
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
		parent::__construct("usernameOrPassIsWrong", "HTTP/1.1 403 FORBIDDEN", 101, $previous);
	}

}

class NotAllowedToSubmitException extends ApiException {
	
	function __construct($message = "unableToSubmit", Exception $previous = NULL) {
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
		parent::__construct("emailNotVerified", "HTTP/1.1 403 FORBIDDEN", 600, $previous);
	}

}


class EmailVerificationSendException extends ApiException {

	/**
	 * 
	 * @param string $message
	 * @param Exception $previous
	 */
	function __construct(Exception $previous = NULL) {
		parent::__construct("errorWhileSendingMail", "HTTP/1.1 500 INTERNAL SERVER ERROR", 601, $previous);
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
	function __construct($message = "problemDeployerFailed", Exception $previous = NULL) {
		parent::__construct($message, 'HTTP/1.1 412 PRECONDITION FAILED', 412, $previous);
	}
}

/**
 * LoginDisabledException
 */
class LoginDisabledException extends ApiException {
	
	function __construct(ApiException $previous = NULL) {
		parent::__construct("loginDisabled", 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
	}
}
