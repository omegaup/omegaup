<?php

 namespace OmegaUp\Controllers;

/**
 * Description of GraderController
 *
 * @psalm-type GraderStatus=array{status: string, broadcaster_sockets: int, embedded_runner: bool, queue: array{running: list<array{name: string, id: int}>, run_queue_length: int, runner_queue_length: int, runners: list<string>}}
 * @psalm-type EphemeralDetailsPayload=array{theme: string}
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
     * @return array{templateProperties: array{fullWidth: bool, hideFooterAndHeader: bool, payload: EphemeralDetailsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getEphemeralDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing. Not logged user can access here
            $r->identity = null;
        }

        return [
            'templateProperties' => [
                'payload' => [
                    // TODO: Here we could send user preferences, like golden-layout theme
                    'theme' => 'vs-dark',
                ],
                'hideFooterAndHeader' => true,
                'fullWidth' => true,
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleGraderEphemeral'
                ),
            ],
            'entrypoint' => 'grader_ephemeralv2',
        ];
    }
}
