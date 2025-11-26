<?php

namespace OmegaUp\Template;

class JsIncludeNode extends \Twig\Node\Node {
    /**
     * @readonly
     * @var array<string, array<string, bool>>
     */
    private static $_includedScripts = [];

    /**
     * @param string[] $options
     */
    public function __construct(
        string $entrypoint,
        array $options,
        int $line,
        ?string $tag = null,
    ) {
        parent::__construct(
            [],
            ['entrypoint' => $entrypoint, 'options' => $options],
            $line,
            $tag
        );
    }

    public function compile(\Twig\Compiler $compiler): void {
        /** @var \Twig\Source */
        $sourceContext = $this->getSourceContext();
        $source = $sourceContext->getName();
        /** @var string */
        $entrypoint = $this->getAttribute('entrypoint');
        /** @var string[] */
        $options = $this->getAttribute('options');
        $compiler->addDebugInfo($this);
        foreach (self::getScriptTags($source, $entrypoint, $options) as $tag) {
            $compiler
                ->raw('echo ')
                ->repr($tag)
                ->raw(";\n");
        }
    }

    /**
     * @param string[] $options
     *
     * @return string[]
     */
    public static function getScriptTags(
        string $sourceName,
        string $entrypoint,
        array $options,
    ): array {
        if (!array_key_exists($sourceName, self::$_includedScripts)) {
            self::$_includedScripts[$sourceName] = [];
        }
        /** @var string[] */
        $omitScripts = [];
        if (in_array('omitRuntime', $options)) {
            foreach (self::getJavaScriptDeps('omegaup') as $filename) {
                $omitScripts[] = $filename;
            }
        }
        /** @var string[] */
        $result = [];
        foreach (self::getJavaScriptDeps($entrypoint) as $filename) {
            if (
                array_key_exists(
                    $filename,
                    self::$_includedScripts[$sourceName]
                )
            ) {
                // Avoid including files that have already been included
                // before.
                continue;
            }
            if (in_array($filename, $omitScripts)) {
                // Avoid including files that have already been included by the
                // runtime.
                continue;
            }
            self::$_includedScripts[$sourceName][$filename] = true;
            // Append a hash to ensure that the cache is invalidated
            // if the content changes.
            $generatedPath = dirname(__DIR__, 3) . "/www/{$filename}";
            $hash = substr(sha1(file_get_contents($generatedPath)), 0, 6);
            $result[] = "<script src=\"{$filename}?ver={$hash}\" type=\"text/javascript\" defer></script>\n";
        }
        return $result;
    }

    /**
     * @return list<string>
     */
    private static function getJavaScriptDeps(string $entrypoint): array {
        $jsonPath = dirname(
            __DIR__,
            3
        ) . "/www/js/dist/{$entrypoint}.deps.json";
        $textContents = @file_get_contents($jsonPath);
        if ($textContents === false) {
            die(
                'Please run <tt style="background: #eee">cd /opt/omegaup && yarn install && yarn run dev-all</tt>.'
            );
        }
        $jsonContents = json_decode($textContents, associative: true);
        if (is_null($jsonContents)) {
            die('Invalid JSON in dependency file: ' . $jsonPath);
        }
        /** @var array{css: list<string>, js: list<string>} $jsonContents */
        return $jsonContents['js'];
    }
}
