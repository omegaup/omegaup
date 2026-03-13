<?php

namespace OmegaUp\Exceptions;

class InvalidParameterException extends \OmegaUp\Exceptions\ApiException {
    /**
     * @var null|string
     */
    public $parameter;

    /** @var array<string, string> */
    private $additionalParameters;

    /**
     * @param string $message
     * @param null|string $parameter
     * @param array<string, string> $additionalParameters
     */
    public function __construct(
        string $message,
        ?string $parameter = null,
        array $additionalParameters = []
    ) {
        parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400);
        $this->parameter = $parameter;
        $this->additionalParameters = $additionalParameters;
        $this->addCustomMessageToArray('parameter', $parameter);
    }

    public function getErrorMessage(): string {
        /**
         * @psalm-suppress TranslationStringNotALiteralString this is being
         * checked from the constructor of the exception
         */
        $localizedText = \OmegaUp\Translations::getInstance()->get(
            $this->message
        );
        if (empty($localizedText)) {
            self::$log->error("Untranslated error message: {$this->message}");
            return "{untranslated:{$this->message}}";
        }
        $localizedText = \OmegaUp\ApiUtils::formatString(
            $localizedText,
            $this->additionalParameters
        );
        if (is_null($this->parameter)) {
            return $localizedText;
        }
        // Try to translate the parameter name
        $parameterNameKey = "parameterName_{$this->parameter}";
        /**
         * @psalm-suppress TranslationStringNotALiteralString parameter name
         * translation keys are constructed dynamically
         */
        $translatedParameterName = \OmegaUp\Translations::getInstance()->get(
            $parameterNameKey
        );
        // If translation doesn't exist or is empty, use the original parameter name
        if (
            empty(
                $translatedParameterName
            ) || $translatedParameterName === "{untranslated:{$parameterNameKey}}"
        ) {
            $translatedParameterName = $this->parameter;
        }
        return "{$translatedParameterName}: $localizedText";
    }
}
