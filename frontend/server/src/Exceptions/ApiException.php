<?php

namespace OmegaUp\Exceptions;

/**
 * Exception that works with arrays instead of plain strings
 */
abstract class ApiException extends \Exception {
    /** @var \Logger */
    public static $log;

    /** @var string */
    protected $header;

    /** @var array<string, mixed> */
    private $_customMessage;

    /**
     * Builds an API exception
     */
    public function __construct(
        string $message,
        string $header,
        int $code,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->header = $header;
        $this->_customMessage = [];
    }

    /**
     * Adds a custom field to the asArray representation of this exception
     *
     * @param string $key
     * @param mixed $value
     */
    final public function addCustomMessageToArray(string $key, $value): void {
        $this->_customMessage[$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    final public function asArray(): array {
        $previous = $this->getPrevious();
        return array_merge(
            [
                'status' => 'error',
                'error' => $this->getErrorMessage(),
                'errorcode' => $this->code,
                'header' => $this->header,
                'cause' => !is_null($previous) ? $previous->getMessage() : null,
                'trace' => $this->getTraceAsString(),
            ],
            $this->_customMessage
        );
    }

    /**
     * Returns exception info intended for public error msgs in http responses
     *
     * @return array<string, mixed>
     */
    final public function asResponseArray(): array {
        return array_merge(
            [
                'status' => 'error',
                'error' => $this->getErrorMessage(),
                'errorname' => $this->message,
                'errorcode' => $this->code,
                'header' => $this->header,
            ],
            $this->_customMessage
        );
    }

    public function getErrorMessage(): string {
        $localizedText = \OmegaUp\Translations::getInstance()->get(
            $this->message
        );
        if (is_null($localizedText)) {
            self::$log->error("Untranslated error message: {$this->message}");
            return "{untranslated:{$this->message}}";
        }
        return $localizedText;
    }
}

\OmegaUp\Exceptions\ApiException::$log = \Logger::getLogger('ApiException');
