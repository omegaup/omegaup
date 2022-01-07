<?php

namespace OmegaUp\Template;

class VersionHashParser extends \Twig\TokenParser\AbstractTokenParser {
    public function parse(\Twig\Token $token): \Twig\Node\Node {
        $parser = $this->parser;
        $stream = $parser->getStream();

        /** @var string */
        $src = $stream->expect(\Twig\Token::STRING_TYPE)->getValue();
        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new \OmegaUp\Template\VersionHashNode(
            $src,
            $token->getLine(),
            $this->getTag(),
        );
    }

    public function getTag(): string {
        return 'versionHash';
    }
}
