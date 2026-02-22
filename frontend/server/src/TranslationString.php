<?php

namespace OmegaUp;

/**
 * Class to identify a string as being a translation string.
 */
class TranslationString implements \JsonSerializable {
    /**
     * @param array<string, mixed> $args
     */
    public function __construct(
        public readonly string $message,
        /** @var array<string, mixed> */
        public readonly array $args = [],
    ) {
    }

    public function __toString(): string {
        return $this->message;
    }

    public function jsonSerialize(): mixed {
        return $this->message;
    }
}
