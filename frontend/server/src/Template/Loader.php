<?php

namespace OmegaUp\Template;

class Loader implements \Twig\Loader\LoaderInterface {
    /**
     * @readonly
     * @var \Twig\Loader\FilesystemLoader
     */
    private $_loader;

    public function __construct() {
        $this->_loader = new \Twig\Loader\FilesystemLoader(
            dirname(__DIR__, 3) . '/templates/',
        );
    }

    public function getSourceContext(string $name): \Twig\Source {
        $originalSource = $this->_loader->getSourceContext('template.tpl');
        return new \Twig\Source(
            $originalSource->getCode(),
            $name,
            $originalSource->getPath(),
        );
    }

    public function getCacheKey(string $name): string {
        return $name;
    }

    public function isFresh(string $name, int $time): bool {
        /** @psalm-suppress TypeDoesNotContainType this can change depending on environment */
        if (
            defined('OMEGAUP_ENVIRONMENT') &&
            OMEGAUP_ENVIRONMENT === 'development'
        ) {
            return false;
        }
        return true;
    }

    public function exists(string $name): bool {
        return true;
    }
}
