<?php

/**
 * DuplicatedEntryInDatabaseException
 *
 */
class DuplicatedEntryInDatabaseException extends \OmegaUp\Exceptions\ApiException {
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
class DuplicatedEntryInArrayException extends \OmegaUp\Exceptions\ApiException {
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
class DatabaseOperationException extends \OmegaUp\Exceptions\ApiException {
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
class NotFoundException extends \OmegaUp\Exceptions\ApiException {
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
class ForbiddenAccessException extends \OmegaUp\Exceptions\ApiException {
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
class UnauthorizedException extends \OmegaUp\Exceptions\ApiException {
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
class PreconditionFailedException extends \OmegaUp\Exceptions\ApiException {
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
class InvalidFilesystemOperationException extends \OmegaUp\Exceptions\ApiException {
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
class CaptchaVerificationFailedException extends \OmegaUp\Exceptions\ApiException {
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
class InternalServerErrorException extends \OmegaUp\Exceptions\ApiException {
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
class InvalidCredentialsException extends \OmegaUp\Exceptions\ApiException {
    /**
     *
     * @param string $message
     * @param Exception $previous
     */
    public function __construct(Exception $previous = null) {
        parent::__construct('usernameOrPassIsWrong', 'HTTP/1.1 403 FORBIDDEN', 101, $previous);
    }
}

class NotAllowedToSubmitException extends \OmegaUp\Exceptions\ApiException {
    public function __construct($message = 'unableToSubmit', Exception $previous = null) {
        parent::__construct($message, 'HTTP/1.1 403 FORBIDDEN', 403, $previous);
    }
}

class EmailNotVerifiedException extends \OmegaUp\Exceptions\ApiException {
    /**
     *
     * @param string $message
     * @param Exception $previous
     */
    public function __construct(Exception $previous = null) {
        parent::__construct('emailNotVerified', 'HTTP/1.1 403 FORBIDDEN', 600, $previous);
    }
}

class EmailVerificationSendException extends \OmegaUp\Exceptions\ApiException {
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
class ProblemDeploymentFailedException extends \OmegaUp\Exceptions\ApiException {
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
        $localizedText = \OmegaUp\Translations::getInstance()->get($this->message);
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
class LoginDisabledException extends \OmegaUp\Exceptions\ApiException {
    public function __construct($message, \OmegaUp\Exceptions\ApiException $previous = null) {
        parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
    }
}

class UnimplementedException extends \OmegaUp\Exceptions\ApiException {
    public function __construct(\OmegaUp\Exceptions\ApiException $previous = null) {
        parent::__construct('wordsUnimplemented', 'HTTP/1.1 405 Method Not Allowed', 405, $previous);
    }
}

class CSRFException extends \OmegaUp\Exceptions\ApiException {
    public function __construct($message = 'csrfException', \OmegaUp\Exceptions\ApiException $previous = null) {
        parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
    }
}
