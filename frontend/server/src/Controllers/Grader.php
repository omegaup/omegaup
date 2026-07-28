<?php

 namespace OmegaUp\Controllers;

/**
 * Description of GraderController
 *
 * @psalm-type GraderStatus=array{status: string, broadcaster_sockets: int, embedded_runner: bool, queue: array{running: list<array{name: string, id: int}>, run_queue_length: int, runner_queue_length: int, runners: list<string>}}
 * @psalm-type FullIDEPayload=array{acceptedLanguages: list<string>, preferredLanguage: null | string}
 * @psalm-type ViewUnavailablePayload=array{description: string}
 */
class Grader extends \OmegaUp\Controllers\Controller {
    /**
     * Calls to /status grader
     *
     * @return array{grader: GraderStatus}
     */
    public static function apiStatus(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return [
            'grader' => \OmegaUp\Grader::getInstance()->status(),
        ];
    }
    /**
     * @return array{templateProperties: array{payload: FullIDEPayload|ViewUnavailablePayload, title: \OmegaUp\TranslationString, fullWidth?: bool, hideFooterAndHeader?: bool}, entrypoint: string}
    */
    public static function getGraderForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->user = null;
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // do nothing
        }

        $preferredLanguage = \OmegaUp\DAO\Users::getPreferredLanguage(
            $r->user?->user_id
        );

        $ephemeralGraderEnabled = \OmegaUp\DAO\SystemSettings::getBooleanSetting(
            'ephemeral_grader_enabled',
            true
        );

        $title = new \OmegaUp\TranslationString('problemlessGrader');
        $payload = [
            'acceptedLanguages' => \OmegaUp\Controllers\Run::DEFAULT_LANGUAGES(),
            'preferredLanguage' => $preferredLanguage,
        ];

        if (!$ephemeralGraderEnabled) {
            return [
                'templateProperties' => [
                    'title' => $title,
                    'payload' => [
                        'description' => \OmegaUp\Translations::getInstance()->get(
                            'ephemeralGraderDisabled'
                        ),
                    ],
                ],
                'entrypoint' => 'common_view_unavailable',
            ];
        }

        return [
            'templateProperties' => [
                'title' => $title,
                'fullWidth' => true,
                'hideFooterAndHeader' => true,
                'payload' => $payload,
            ],
            'entrypoint' => 'grader_ide',
        ];
    }
}
