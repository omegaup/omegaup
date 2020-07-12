<?php

namespace OmegaUp;

class UrlHelper {
    /**
     * Wrapper of file_get_contents
     *
     * @param string $url
     * @param null|resource $context
     */
    public function fetchUrl(string $url, $context = null): string {
        return file_get_contents($url, /*use_include_path=*/false, $context);
    }
}
