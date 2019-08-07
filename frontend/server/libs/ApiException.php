<?php

require_once 'libs/Translations.php';

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
        $this->customMessage = [];
    }

    /**
     * Returns header
     *
     * @return string
     */
    final public function getHeader() : string {
        return $this->header;
    }

    /**
     * Adds a custom field to the asArray representation of this exception
     *
     * @param string $key
     * @param type $value
     */
    final public function addCustomMessageToArray($key, $value) : void {
        $this->customMessage[$key] = $value;
    }

    /**
     *
     * @return array
     */
    final public function asArray() : array {
        return array_merge(
            [
                'status' => 'error',
                'error' => $this->getErrorMessage(),
                'errorcode' => $this->code,
                'header' => $this->header,
                'cause' => !is_null($this->getPrevious()) ? $this->getPrevious()->getMessage() : null,
                'trace' => $this->getTraceAsString(),
            ],
            $this->customMessage
        );
    }

    /**
     * Returns exception info intended for public error msgs in http responses
     *
     * @return array
     */
    final public function asResponseArray() : array {
        return array_merge(
            [
                'status' => 'error',
                'error' => $this->getErrorMessage(),
                'errorname' => $this->message,
                'errorcode' => $this->code,
                'header' => $this->header,
            ],
            $this->customMessage
        );
    }

    public function getErrorMessage() : string {
        if (is_null($this->message)) {
            self::$log->error('null error message');
            return '{untranslated:(null)}';
        }
        $localizedText = Translations::getInstance()->get($this->message);
        if (is_null($localizedText)) {
            self::$log->error("Untranslated error message: {$this->message}");
            return "{untranslated:{$this->message}}";
        }
        return $localizedText;
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
    public function __construct($message, $parameter = null, $additional_parameters = []) {
        parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400);
        $this->parameter = $parameter;
        $this->additional_parameters = $additional_parameters;
    }

    public function getErrorMessage() : string {
        $localizedText = Translations::getInstance()->get($this->message);
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
 * DuplicatedEntryInArrayException
 *
 */
class DuplicatedEntryInArrayException extends ApiException {
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
 * DatabaseOperationException
 */
class DatabaseOperationException extends ApiException {
    private $_message = null;
    private $_errno = 0;

    /**
     * @param string $message The error message.
     * @param bool $isDuplicate Whether this was raised from there being a duplicate entry.
     */
    public function __construct(string $message, int $errno) {
        parent::__construct('generalError', 'HTTP/1.1 400 Bad Request', 400);
        $this->_message = $message;
        $this->_errno = $errno;
    }

    public function __toString() : string {
        return "{$this->_message}: " . parent::__toString();
    }

    public function isDuplicate() : bool {
        return $this->_errno == 1062;
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
    public function __construct($message = 'problemDeployerFailed', $context = null) {
        parent::__construct($message, 'HTTP/1.1 412 PRECONDITION FAILED', 412);
        $this->context = $context;
    }

    public function getErrorMessage() : string {
        $localizedText = Translations::getInstance()->get($this->message);
        if (empty($localizedText)) {
            self::$log->error("Untranslated error message: {$this->message}");
            return "{untranslated:{$this->message}}";
        }
        if (!empty($this->context)) {
            $localizedText .= ": {$this->context}";
        }
        return $localizedText;
    }
}

/**
 * LoginDisabledException
 */
class LoginDisabledException extends ApiException {
    public function __construct($message, ApiException $previous = null) {
        parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
    }
}

class UnimplementedException extends ApiException {
    public function __construct(ApiException $previous = null) {
        parent::__construct('wordsUnimplemented', 'HTTP/1.1 405 Method Not Allowed', 405, $previous);
    }
}

class CSRFException extends ApiException {
    public function __construct($message = 'csrfException', ApiException $previous = null) {
        parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
    }
}
