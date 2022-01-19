<?php

namespace OmegaUp\Template;

class VersionHashNode extends \Twig\Node\Node {
    /**
     * @readonly
     * @var array<string, bool>
     */
    private static $_includedScripts = [];

    public function __construct(
        string $src,
        int $line,
        ?string $tag = null,
    ) {
        parent::__construct([], ['src' => $src], $line, $tag);
    }

    public function compile(\Twig\Compiler $compiler): void {
        /** @var string */
        $src = $this->getAttribute('src');
        $path = dirname(__DIR__, 3) . "/www/{$src}";
        $hash = '000000';
        if (is_file($path)) {
            $hash = substr(sha1(file_get_contents($path)), 0, 6);
        }
        $compiler
            ->addDebugInfo($this)
            ->raw('echo ')
            ->repr("{$src}?ver={$hash}")
            ->raw(";\n");
    }
}
