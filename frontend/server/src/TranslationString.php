<?php

namespace OmegaUp;

/**
 * Class to identify a string as being a translation string.
 */
class TranslationString implements \JsonSerializable {
    /**
     * @var string
     * @readonly
     */
    public $message;

    /**
     * @var array<string, mixed>
     * @readonly
     */
    public $args;

    /**
     * @param array<string, mixed> $args
     */
    public function __construct(string $message, array $args = []) {
        $this->message = $message;
        $this->args = $args;
    }

    public function __toString(): string {
        return $this->message;
    }

    public function jsonSerialize(): string {
        return $this->message;
    }
}
