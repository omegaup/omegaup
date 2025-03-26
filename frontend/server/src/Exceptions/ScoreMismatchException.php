<?php

namespace OmegaUp\Exceptions;

class ScoreMismatchException extends \OmegaUp\Exceptions\ApiException {
    /**
     * @var float
     */
    public float $originalScore;

    /**
     * @var float
     */
    public float $currentScore;

    /**
     * @var string
     */
    public string $version;

    public function __construct(
        string $message,
        float $originalScore,
        float $currentScore,
        string $version,
        ?\Exception $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 500 INTERNAL SERVER ERROR',
            500,
            $previous
        );
        $this->originalScore = $originalScore;
        $this->currentScore = $currentScore;
        $this->version = $version;
    }

    public function getErrorMessage(): string {
        /**
         * @psalm-suppress TranslationStringNotALiteralString this is being
         * checked from the constructor of the exception
         */
        $localizedText = \OmegaUp\Translations::getInstance()->get(
            $this->message
        );
        return \OmegaUp\ApiUtils::formatString(
            $localizedText,
            [
                'originalScore' => $this->originalScore,
                'currentScore' => $this->currentScore,
                'version' => $this->version,
            ],
        );
    }
}
