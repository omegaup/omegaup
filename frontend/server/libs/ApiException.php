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
    public function __construct($message, $header, $code, Exception $previous = null) {
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
            'status' => 'error',
            'error' => $this->getErrorMessage(),
            'errorcode' => $this->code,
            'header' => $this->header,
            'cause' => !is_null($this->getPrevious()) ? $this->getPrevious()->getMessage() : null,
            'trace' => $this->getTraceAsString(),
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
            'status' => 'error',
            'error' => $this->getErrorMessage(),
            'errorname' => $this->message,
            'errorcode' => $this->code,
            'header' => $this->header
        );

        return array_merge($arrayToReturn, $this->customMessage);
    }

    protected function getErrorMessage() {
        // obtener el texto final (ya localizado) de smarty.
        global $smarty;
        $localizedText = $smarty->getConfigVars($this->message);
        if (empty($localizedText)) {
            self::$log->error("Untranslated error message: {$this->message}");
            return "{untranslated:{$this->message}}";
        } else {
            return $localizedText;
        }
    }
}

ApiException::$log = Logger::getLogger('ApiException');

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
    public function __construct($message, $parameter = null, $additional_parameters = array()) {
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
            return "{untranslated:{$this->message}}";
        }
        $localizedText = ApiUtils::FormatString(
            $localizedText,
            $this->additional_parameters
        );
        if ($this->parameter == null) {
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
    public function __construct($message, Exception $previous = null) {
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
    public function __construct(Exception $previous = null) {
        parent::__construct('generalError', 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
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
    public function __construct($message = 'notFound', Exception $previous = null) {
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
    public function __construct($message = 'userNotAllowed', Exception $previous = null) {
        parent::__construct($message, 'HTTP/1.1 403 FORBIDDEN', 403, $previous);
    }
}

/**
 * UnauthorizedException
 *
 */
class UnauthorizedException extends ApiException {
    /**
     *
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message = 'loginRequired', Exception $previous = null) {
        parent::__construct($message, 'HTTP/1.1 401 UNAUTHORIZED', 401, $previous);
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
    public function __construct($message = 'userNotAllowed', Exception $previous = null) {
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
    public function __construct($message = 'generalError', Exception $previous = null) {
        parent::__construct($message, 'HTTP/1.1 500 INTERNAL SERVER ERROR', 500, $previous);
    }
}

/**
 * Default for unexpected errors
 *
 */
class CaptchaVerificationFailedException extends ApiException {
    /**
     *
     * @param Exception $previous
     */
    public function __construct(Exception $previous = null) {
        parent::__construct('unableToVerifyCaptcha', 'HTTP/1.1 500 INTERNAL SERVER ERROR', 500, $previous);
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
    public function __construct(Exception $previous = null) {
        parent::__construct('generalError', 'HTTP/1.1 500 INTERNAL SERVER ERROR', 500, $previous);
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
    public function __construct(Exception $previous = null) {
        parent::__construct('usernameOrPassIsWrong', 'HTTP/1.1 403 FORBIDDEN', 101, $previous);
    }
}

class NotAllowedToSubmitException extends ApiException {
    public function __construct($message = 'unableToSubmit', Exception $previous = null) {
        parent::__construct($message, 'HTTP/1.1 403 FORBIDDEN', 403, $previous);
    }
}

class EmailNotVerifiedException extends ApiException {
    /**
     *
     * @param string $message
     * @param Exception $previous
     */
    public function __construct(Exception $previous = null) {
        parent::__construct('emailNotVerified', 'HTTP/1.1 403 FORBIDDEN', 600, $previous);
    }
}

class EmailVerificationSendException extends ApiException {
    /**
     *
     * @param string $message
     * @param Exception $previous
     */
    public function __construct(Exception $previous = null) {
        parent::__construct('errorWhileSendingMail', 'HTTP/1.1 500 INTERNAL SERVER ERROR', 601, $previous);
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
    public function __construct($message = 'problemDeployerFailed', Exception $previous = null) {
        parent::__construct($message, 'HTTP/1.1 412 PRECONDITION FAILED', 412, $previous);
    }
}

/**
 * LoginDisabledException
 */
class LoginDisabledException extends ApiException {
    public function __construct(ApiException $previous = null) {
        parent::__construct('loginDisabled', 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
    }
}
