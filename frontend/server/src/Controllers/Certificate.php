<?php

namespace OmegaUp\Controllers;

/**
 * CertificateController

 * @psalm-type CertificateDetailsPayload=array{uuid: string}
 */
class Certificate extends \OmegaUp\Controllers\Controller {
    /** @var list<string> */
    private static $months = ['Diciembre', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

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

    private static function getCoderOfTheMonthCertificate(
        string $verification_code,
        bool $isFemaleCategory
    ): string {
        $certificateData = \OmegaUp\DAO\Certificates::getCoderOfTheMonthCertificateByVerificationCode(
            $verification_code
        );

        if (is_null($certificateData)) {
            return '';
        }

        $title = 'Coder del mes';
        if ($isFemaleCategory) {
            $title .= ' para ellas';
        }
        $identityName = $certificateData['identity_name'];
        $date = $certificateData['timestamp']->time;
        $month = intval(date('n', $date));
        $description = 'por su desempeño en el mes de ' . self::$months[$month - 1] . ' al obtener más puntos en la plataforma omegaUp.com';

        return self::createCertificatePdf(
            $title,
            $identityName,
            $description,
            $date
        );
    }
}
