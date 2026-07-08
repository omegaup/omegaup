<?php

namespace OmegaUp\Exceptions;

class InternalServerErrorException extends \OmegaUp\Exceptions\ApiException {
    /** @var array<string, string> */
    private $additionalParameters;

    /**
     * @param string $message
     * @param ?\Exception $previous
     * @param array<string, string> $additionalParameters
    */
    public function __construct(
        string $message = 'generalError',
        ?\Exception $previous = null,
        array $additionalParameters = []
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 500 INTERNAL SERVER ERROR',
            500,
            $previous
        );
        $this->additionalParameters = $additionalParameters;
    }

    public function getErrorMessage(): string {
        /**
         * @psalm-suppress TranslationStringNotALiteralString
         */
        $localizedText = \OmegaUp\Translations::getInstance()->get(
            $this->message
        );

        if (empty($localizedText)) {
            self::$log->error("Untranslated error message: {$this->message}");
            return "{untranslated:{$this->message}}";
        }

        return \OmegaUp\ApiUtils::formatString(
            $localizedText,
            $this->additionalParameters
        );
    }
}
