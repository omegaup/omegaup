<?php

namespace OmegaUp\Exceptions;

class DuplicatedEntryInArrayException extends \OmegaUp\Exceptions\ApiException {
    /**
     * @var string[]
     */
    public array $duplicatedItemsInArray;

    /**
     * @var string
     */
    public string $duplicatedItem;

    /**
     * @param string[] $duplicatedItemsInArray
     */
    public function __construct(
        string $message,
        string $duplicatedItem,
        array $duplicatedItemsInArray,
        ?\Exception $previous = null
    ) {
        parent::__construct(
            $message,
            'HTTP/1.1 400 BAD REQUEST',
            400,
            $previous
        );
        $this->duplicatedItemsInArray = $duplicatedItemsInArray;
        $this->duplicatedItem = $duplicatedItem;
    }

    public function getErrorMessage(): string {
        /**
         * @psalm-suppress TranslationStringNotALiteralString this is being
         * checked from the constructor of the exception
         */
        $localizedText = \OmegaUp\Translations::getInstance()->get(
            $this->message
        );
        if (empty($this->duplicatedItemsInArray)) {
            return $localizedText;
        }
        return \OmegaUp\ApiUtils::formatString(
            $localizedText,
            [$this->duplicatedItem => join(
                '<br />',
                $this->duplicatedItemsInArray
            )]
        );
    }
}
