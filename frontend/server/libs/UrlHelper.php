<?php

class UrlHelper {
    /**
     * Wrapper of file_get_contents
     *
     * @param string $filename
     * @param bool $use_include_path
     * @param resource $context
     */
    public function fetchUrl($filename, $use_include_path, $context) {
        return file_get_contents($filename, $use_include_path, $context);
    }
}
