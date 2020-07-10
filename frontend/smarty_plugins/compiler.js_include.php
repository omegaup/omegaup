<?php

/**
 * @return list<string>
 */
function getJavaScriptDeps(string $entrypoint): array {
    $jsonPath = __DIR__ . "/../www/js/dist/{$entrypoint}.deps.json";
    $textContents = @file_get_contents($jsonPath);
    if ($textContents === false) {
        die(
            'Please run <tt style="background: #eee">cd /opt/omegaup && yarn install && yarn run dev-all</tt>.'
        );
    }
    /** @var array{css: list<string>, js: list<string>} */
    $jsonContents = json_decode($textContents, /*assoc=*/true);
    return $jsonContents['js'];
}

/**
 * @param array<string, string> $params
 * @param \Smarty $smarty
 */
function smarty_compiler_js_include(
    array $params,
    \Smarty $smarty
): string {
    $entrypoint = $params['entrypoint'];
    if ($entrypoint[0] == '"' || $entrypoint[0] == "'") {
        $entrypoint = substr($entrypoint, 1, strlen($entrypoint) - 2);
    }
    $runtimePaths = [];
    if (!in_array("'runtime'", array_values($params))) {
        // 'omegaup' is the entrypoint that contains the runtime.
        $runtimePaths = getJavaScriptDeps('omegaup');
    }
    $generatedPaths = [];
    foreach (getJavaScriptDeps($entrypoint) as $filename) {
        if (in_array($filename, $runtimePaths)) {
            // Avoid including files that have already been
            // included by the runtime.
            continue;
        }
        // Append a hash to ensure that the cache is invalidated
        // if the content changes.
        $generatedPath = __DIR__ . "/../www/{$filename}";
        $hash = substr(sha1(file_get_contents($generatedPath)), 0, 6);
        $generatedPaths[] = "<script src=\"{$filename}?ver={$hash}\" type=\"text/javascript\" defer></script>";
    }
    return implode('', $generatedPaths);
}
