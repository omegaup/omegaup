<?php

// From https://gist.github.com/Xeoncross/dc2ebf017676ae946082
function prefered_language(array $available_languages, $http_accept_language) {
    $available_languages = array_flip($available_languages);

    $langs;
    preg_match_all('~([\w-]+)(?:[^,\d]+([\d.]+))?~', strtolower($http_accept_language), $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        list($a, $b) = explode('-', $match[1]) + ['', ''];
        $value = isset($match[2]) ? (float) $match[2] : 1.0;

        if (isset($available_languages[$match[1]])) {
            $langs[$match[1]] = $value;
            continue;
        }

        if (isset($available_languages[$a])) {
            $langs[$a] = $value - 0.1;
        }
    }
    if ($langs) {
        arsort($langs);
        return key($langs);
    } else {
        return key($available_languages);
    }
}

$languages = ['en', 'es'];
$preferred = $languages[0];

if (in_array('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
    $preferred = preferred_language($languages, $_SERVER['HTTP_ACCEPT_LANGUAGE']);
}

$location = $_SERVER['REQUEST_URI'];
if ($location[strlen($location) - 1] != '/') {
    $location .= '/';
}

header("Location: {$location}{$preferred}/");
