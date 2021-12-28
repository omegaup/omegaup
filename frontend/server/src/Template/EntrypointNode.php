<?php

namespace OmegaUp\Template;

class EntrypointNode extends \Twig\Node\Node {
    public function __construct(
        int $line,
        ?string $tag = null,
    ) {
        parent::__construct([], [], $line, $tag);
    }

    public function compile(\Twig\Compiler $compiler): void {
        /** @var \Twig\Source */
        $sourceContext = $this->getSourceContext();
        $entrypoint = $sourceContext->getName();
        $options = ['omitRuntime'];
        $compiler->addDebugInfo($this);
        foreach (
            \OmegaUp\Template\JsIncludeNode::getScriptTags(
                $entrypoint,
                $entrypoint,
                $options,
            ) as $tag
        ) {
            $compiler
                ->raw('echo ')
                ->repr($tag)
                ->raw(";\n");
        }
    }
}
