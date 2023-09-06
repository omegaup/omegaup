<?php

namespace OmegaUp\Controllers;

use setasign\Fpdi\Fpdi;
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
        $pdf = new FPDI('L');

        $pdf->setSourceFile('/opt/omegaup/stuff/CertificateTemplate.pdf');
        $templateId = $pdf->importPage(1);
        $pdf->addPage();
        $pdf->useTemplate($templateId);

        $pdf->SetFont('Arial', 'B', 39);
        $pdf->SetXY(50, 76);
        $pdf->Cell(215, 15, utf8_decode($title), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 21);
        $pdf->SetXY(50, 109);
        $pdf->Cell(215, 15, utf8_decode($identityName), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 18);
        $pdf->SetXY(50, 132);
        $pdf->MultiCell(215, 10, utf8_decode($description), 0, 'C');

        $pdf->SetFont('Arial', 'I', 14);
        $pdf->SetTextColor(153, 153, 153);
        $pdf->SetXY(50, 151);
        $day = date('j', $date);
        $month = date('n', $date);
        $year = date('o', $date);
        $pdf->Cell(
            215,
            10,
            utf8_decode(
                'Bellevue, Washington a ' . $day . ' de ' . self::$months[$month] . ' de ' . $year
            ),
            0,
            1,
            'C'
        );

        return $pdf->Output('', 'S');
    }

    private static function getContestCertificate(string $verification_code): string {
        $certificateData = \OmegaUp\DAO\Certificates::getContestCertificateByVerificationCode(
            $verification_code
        );

        $title = $certificateData['contest_place'] . '° lugar';
        $identityName = $certificateData['identity_name'];
        $description = 'por su participación en el concurso "' . $certificateData['contest_title'] . '" que se llevó a cabo en la plataforma omegaUp.com';
        $date = $certificateData['timestamp']->time;

        return self::createCertificatePdf(
            $title,
            $identityName,
            $description,
            $date
        );
    }

    /**
     * API to generate the certificate PDF
     *
     * @return array{certificate: string}
     *
     * @omegaup-request-param string $verification_code
     */
    public static function apiGetCertificatePdf(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $verification_code = $r->ensureString('verification_code');
        $type = \OmegaUp\DAO\Certificates::getCertificateTypeByVerificationCode(
            $verification_code
        );

        if ($type === 'contest') {
            return [
                'certificate' => self::getContestCertificate(
                    $verification_code
                ),
            ];
        }
        return [
            'certificate' => '',
        ];
    }
}
