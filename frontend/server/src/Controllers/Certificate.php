<?php

namespace OmegaUp\Controllers;

/**
 * CertificateController

 * @psalm-type CertificateDetailsPayload=array{uuid: string}
 */
class Certificate extends \OmegaUp\Controllers\Controller {
    /**
     * @return array{templateProperties: array{payload: CertificateDetailsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $uuid
     */
    public static function getDetailsForTypeScript(\OmegaUp\Request $r) {
        return [
            'templateProperties' => [
                'payload' => [
                    'uuid' => $r->ensureString('uuid'),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCertificate'
                ),
            ],
            'entrypoint' => 'certificate_details',
        ];
    }

    /**
     * Creates a Clarification for a contest or an assignment of a course
     *
     * @return Clarification
     *
     * @omegaup-request-param mixed $contest_id
     */
    public static function apiGenerateContestCertificates(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();
        //error_log(print_r('hoka',true));
        //error_log(print_r($r,true));
        //error_log(print_r('hola',true));
        // solo certficates generator podran utilizar esta api
        // checar que sean certificates generator

        // obtain the contest
        $contest = \OmegaUp\DAO\Contests::getByPK($r['contest_id']);

        if ($contest->certificates_status === 'uninitiated' || $contest->certificates_status === 'retryable_error') {
            // se guarda certificate_cutoff en la tabla de concursos y se envía
            //un mensaje a la cola contest_certificate
        }

        return [
            'status' => 'ok',
        ];
    }
}
