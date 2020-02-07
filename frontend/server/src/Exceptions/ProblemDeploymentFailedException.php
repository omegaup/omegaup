<?php

namespace OmegaUp\Exceptions;

class ProblemDeploymentFailedException extends \OmegaUp\Exceptions\ApiException {
    /** @var null|string|int */
    private $_context;

    /** @var array<string, string> */
    private $additionalParameters;

    /**
     * @param string $message
     * @param null|string|int $context
     * @param array<string, string> $additionalParameters
     */
    public function __construct(
        $message = 'problemDeployerFailed',
        $context = null,
        array $additionalParameters = []
    ) {
        parent::__construct($message, 'HTTP/1.1 412 PRECONDITION FAILED', 412);
        $this->_context = $context;
        $this->additionalParameters = $additionalParameters;
    }

    public function getErrorMessage(): string {
        $localizedText = \OmegaUp\Translations::getInstance()->get(
            $this->message
        );
        if (empty($localizedText)) {
            self::$log->error("Untranslated error message: {$this->message}");
            return "{untranslated:{$this->message}}";
        }
        if (!empty($this->_context)) {
            $localizedText .= ": {$this->_context}";
        }
        return \OmegaUp\ApiUtils::formatString(
            $localizedText,
            $this->additionalParameters
        );
    }
}
