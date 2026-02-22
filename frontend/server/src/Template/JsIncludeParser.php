<?php

namespace OmegaUp\Template;

class JsIncludeParser extends \Twig\TokenParser\AbstractTokenParser {
    public function parse(\Twig\Token $token): \Twig\Node\Node {
        $parser = $this->parser;
        $stream = $parser->getStream();

        /** @var string */
        $entrypoint = $stream->expect(\Twig\Token::STRING_TYPE)->getValue();
        /** @var string[] */
        $options = [];
        while ($option = $stream->nextIf(\Twig\Token::NAME_TYPE) !== null) {
            /** @var string */
            $value = $option->getValue();
            $options[] = $value;
        }
        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new \OmegaUp\Template\JsIncludeNode(
            $entrypoint,
            $options,
            $token->getLine(),
            $this->getTag(),
        );
    }

    public function getTag(): string {
        return 'jsInclude';
    }
}
