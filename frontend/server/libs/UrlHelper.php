<?php

class UrlHelper {
    /**
     * Wrapper of file_get_contents
     *
     * @param string $url
     * @param resource $context
     */
    public function fetchUrl($url, $context = null) {
        return file_get_contents($url, false /*use_include_path*/, $context);
    }
}
