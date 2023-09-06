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

    private static function createCertificatePdf(
        string $title,
        string $identityName,
        string $description,
        int $date
    ): string {
        return '';
    }

    private static function getCourseCertificate(string $verification_code): string {
        $certificateData = \OmegaUp\DAO\Certificates::getCourseCertificateByVerificationCode(
            $verification_code
        );

        if (is_null($certificateData)) {
            return '';
        }

        $title = 'Graduación';
        $identityName = $certificateData['identity_name'];
        $description = 'por completar el curso "' . $certificateData['course_name'] . '" en la plataforma omegaUp.com';
        $date = $certificateData['timestamp']->time;

        return self::createCertificatePdf(
            $title,
            $identityName,
            $description,
            $date
        );
    }
}
