<?php

function smarty_compiler_version_hash($params, Smarty $smarty) {
    $src = $params['src'];
    if ($src[0] == '"' || $src[0] == "'") {
        $src = substr($src, 1, strlen($src) - 2);
    }
    $paths = [];
    if ($src =='/js/omegaup/lang.#locale#.js') {
        $src = '/js/omegaup/lang.<?php echo $_smarty_tpl->getConfigVariable("locale"); ?>.js';
        $paths = ['/js/omegaup/lang.es.js', '/js/omegaup/lang.en.js',
            '/js/omegaup/lang.pt.js', '/js/omegaup/lang.pseudo.js'];
    } else {
        $paths[] = $src;
    }
    $hashes = [];
    foreach ($paths as $path) {
        $path = __DIR__ . '/../www/' . $path;
        if (!is_file($path)) {
            $hashes[] = '000000';
        } else {
            $hashes[] = substr(sha1(file_get_contents($path)), 0, 6);
        }
    }
    return $src . '?ver=' . implode(',', $hashes);
}
