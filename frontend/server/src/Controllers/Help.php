<?php

namespace OmegaUp\Controllers;

/**
 * HelpController
 */
class Help extends \OmegaUp\Controllers\Controller {
    /**
     * @return array{entrypoint: string, templateProperties: array{payload: array<string, mixed>, title: \OmegaUp\TranslationString}}
     */
    public static function getDetailsForTypeScript(\OmegaUp\Request $r): array {
        return [
            'templateProperties' => [
                'payload' => [],
                'title' => new \OmegaUp\TranslationString('omegaupTitleHelp'),
            ],
            'entrypoint' => 'common_help',
        ];
    }
}
