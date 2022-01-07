<?php

namespace OmegaUp\Template;

class EntrypointParser extends \Twig\TokenParser\AbstractTokenParser {
    public function parse(\Twig\Token $token): \Twig\Node\Node {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new \OmegaUp\Template\EntrypointNode(
            $token->getLine(),
            $this->getTag(),
        );
    }

    public function getTag(): string {
        return 'entrypoint';
    }
}
