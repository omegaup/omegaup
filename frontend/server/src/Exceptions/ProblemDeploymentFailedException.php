<?php

namespace OmegaUp\Exceptions;

class ProblemDeploymentFailedException extends \OmegaUp\Exceptions\ApiException {
    /** @var null|string|int */
    private $_context;

    /**
     * @param string $message
     * @param null|string|int $context
     */
    public function __construct(
        $message = 'problemDeployerFailed',
        $context = null
    ) {
        parent::__construct($message, 'HTTP/1.1 412 PRECONDITION FAILED', 412);
        $this->_context = $context;
    }

    public function getErrorMessage(): string {
        $localizedText = parent::getErrorMessage();
        if (empty($localizedText)) {
            self::$log->error("Untranslated error message: {$this->message}");
            return "{untranslated:{$this->message}}";
        }
        if (!empty($this->_context)) {
            $localizedText .= ": {$this->_context}";
        }
        return $localizedText;
    }
}
