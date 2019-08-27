<?php

spl_autoload_register(function (string $classname) : bool {
    $components = explode('\\', $classname);
    if (empty($components[0])) {
        array_shift($components);
    }

    // Remove the OmegaUp namespace;
    if (empty($components) || $components[0] != 'OmegaUp') {
        return false;
    }
    array_shift($components);

    $filename = __DIR__ . '/src/' . implode('/', $components) . '.php';
    if (!file_exists($filename)) {
        return false;
    }
    include_once $filename;
    return true;
});
