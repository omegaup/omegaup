<?php

 namespace OmegaUp\Controllers;

/**
 * Description of GraderController
 *
 * @psalm-type GraderStatus=array{status: string, broadcaster_sockets: int, embedded_runner: bool, queue: array{running: list<array{name: string, id: int}>, run_queue_length: int, runner_queue_length: int, runners: list<string>}}
 * @psalm-type FullIDEPayload=array{acceptedLanguages: list<string>, preferredLanguage: null | string}
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
     * @return array{templateProperties: array{payload: FullIDEPayload, title: \OmegaUp\TranslationString, fullWidth?: bool, hideFooterAndHeader?: bool}, entrypoint: string}
     * @omegaup-request-param null|string $auth_token
    */
    public static function getGraderForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $preferredLanguage = \OmegaUp\DAO\Users::getPreferredLanguage(
            \OmegaUp\Controllers\Session::getCurrentSession()['user']->user_id ?? null
        );

        return [
            'templateProperties' => [
                'title' => new \OmegaUp\TranslationString(
                    'problemlessGrader'
                ),
                'fullWidth' => true,
                'hideFooterAndHeader' => true,
                'payload' => [
                    'acceptedLanguages' => \OmegaUp\Controllers\Run::DEFAULT_LANGUAGES,
                    'preferredLanguage' => $preferredLanguage,
                ],
            ],
            'entrypoint' => 'grader_ide',
        ];
    }
}
