<?php

/**
 * From https://gist.github.com/Xeoncross/dc2ebf017676ae946082
 *
 * @param list<string> $availableLanguages
 */
function preferredLanguage(
    array $availableLanguages,
    string $httpAcceptLanguage
): string {
    $availableLanguages = array_flip($availableLanguages);

    /** @var array<string, float> */
    $langs = [];
    if (
        preg_match_all(
            '~([\w-]+)(?:[^,\d]+([\d.]+))?~',
            strtolower($httpAcceptLanguage),
            $matches,
            PREG_SET_ORDER
        ) !== false
    ) {
        /** @var list<string> $match */
        foreach ($matches as $match) {
            list($a, $_b) = explode('-', $match[1]) + ['', ''];
            $value = isset($match[2]) ? floatval($match[2]) : 1.0;

            if (isset($availableLanguages[$match[1]])) {
                $langs[$match[1]] = $value;
                continue;
            }

            if (isset($availableLanguages[$a])) {
                $langs[$a] = $value - 0.1;
            }
        }
    }
    if (!empty($langs)) {
        arsort($langs);
        return strval(key($langs));
    } else {
        return strval(key($availableLanguages));
    }
}

$languages = ['en', 'es'];
$preferred = $languages[0];

if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $preferred = preferredLanguage(
        $languages,
        strval($_SERVER['HTTP_ACCEPT_LANGUAGE'])
    );
}

$location = (
    isset($_SERVER['REQUEST_URI'])
) ? $_SERVER['REQUEST_URI'] : '';
if ($location[strlen($location) - 1] != '/') {
    $location .= '/';
}

header("Location: /libinteractive/{$preferred}/");

