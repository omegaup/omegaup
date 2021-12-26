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
        if ($name === 'template.tpl') {
            return $this->_loader->getSourceContext($name);
        }
        return new \Twig\Source(
            '{% extends "template.tpl" %}' .
            '{% block entrypoint %}' .
            "{% jsInclude \"{$name}\" omitRuntime %}" .
            '{% endblock %}',
            $name,
        );
    }

    public function getCacheKey(string $name): string {
        if ($name === 'template.tpl') {
            return $this->_loader->getCacheKey($name);
        }
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
