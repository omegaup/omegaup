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
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param int|null $user_id
     */
    public static function apiGetUserCertificates(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        error_log(print_r($r['user_id'], true));
        try {
            $r->ensureMainUserIdentity();
            if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
        }

        ///mandar llamar la api
        $response = \OmegaUp\DAO\Certificates::getUserCertificates(
            $r['user_id']
        );

        error_log(print_r($response, true));

        return [
            'status' => 'ok',
        ];
    }
}
