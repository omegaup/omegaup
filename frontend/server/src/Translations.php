<?php

namespace OmegaUp;

/**
 * Utility class to lazily load translation strings to be used in controllers
 * and other libraries.
 */
class Translations {
    /**
     * The static Translations instance.
     *
     * @var null|\OmegaUp\Translations
     */
    private static $_instance = null;

    /**
     * The translation strings.
     *
     * @var array<string, string>
     */
    private $_translations = [];

    /**
     * Creates a new instance of Translations.
     */
    private function __construct() {
        $lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            new \OmegaUp\Request()
        );
        $filename = OMEGAUP_ROOT . "/templates/{$lang}.lang";
        /** @var array<int, string> $match */
        foreach (
            new \RegexIterator(
                new \SplFileObject($filename),
                '/([a-zA-Z0-9_]+) = "(.*)"$/',
                \RegexIterator::GET_MATCH
            ) as $match
        ) {
            $this->_translations[$match[1]] = str_replace(
                ['\\"', '\\n'],
                ['"', "\n"],
                $match[2]
            );
        }
    }

    /**
     * Returns the static singleton instance of Translations.
     *
     * @return \OmegaUp\Translations the singleton instance.
     */
    public static function getInstance(): \OmegaUp\Translations {
        if (is_null(self::$_instance)) {
            self::$_instance = new \OmegaUp\Translations();
        }
        return self::$_instance;
    }

    /**
     * Returns the translation string for the provided key.
     *
     * @param string $key the translation string to look up.
     *
     * @return null|string the translated string.
     */
    public function get(string $key): ?string {
        if (!array_key_exists($key, $this->_translations)) {
            return null;
        }
        return $this->_translations[$key];
    }
}
