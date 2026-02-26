<?php

namespace OmegaUp\Controllers;

/**
 * @psalm-type CodeTesterPayload=array{acceptedLanguages: list<string>, preferredLanguage: ?string}
 */
class CodeTester extends \OmegaUp\Controllers\Controller {
    /**
     * @return array{templateProperties: array{payload: CodeTesterPayload, title: \OmegaUp\TranslationString, fullWidth: bool}, entrypoint: string}
     */
    public static function getCodeTesterForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $identity = null;
        $preferredLanguage = null;

        try {
            $r->ensureIdentity();
            $identity = $r->identity;
            if (!is_null($identity->user_id)) {
                $preferredLanguage = \OmegaUp\DAO\Users::getPreferredLanguage(
                    $identity->user_id
                );
            }
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            self::$log->debug('Anonymous user accessing code tester');
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'acceptedLanguages' => \OmegaUp\Controllers\Run::DEFAULT_LANGUAGES(),
                    'preferredLanguage' => $preferredLanguage,
                ],
                'title' => new \OmegaUp\TranslationString('codeTesterTitle'),
                'fullWidth' => true,
            ],
            'entrypoint' => 'code_tester',
        ];
    }
}
