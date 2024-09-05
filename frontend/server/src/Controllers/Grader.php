<?php

 namespace OmegaUp\Controllers;

/**
 * Description of GraderController
 *
 * @psalm-type GraderStatus=array{status: string, broadcaster_sockets: int, embedded_runner: bool, queue: array{running: list<array{name: string, id: int}>, run_queue_length: int, runner_queue_length: int, runners: list<string>}}
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
     * 
     * @return array{templateProperties: array{title: \OmegaUp\TranslationString}, fullWidth?: bool, hideFooterAndHeader?: bool, entrypoint: string}
     */
    public static function getGraderForTypeScript(
        \OmegaUp\Request $r
    ): array {
        return [
            'templateProperties' => [
                'title' => new \OmegaUp\TranslationString(
                    'omegaUp problemless grader'
                ),
                'fullWidth' => true,
                'hideFooterAndHeader' => true,
            ],
            'entrypoint' => 'ide'
        ];
    }
}
