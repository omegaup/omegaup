<?php

namespace OmegaUp\Controllers;

use setasign\Fpdi\Fpdi;
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

    private static function getMonthName(int $month): string {
        $translator = \OmegaUp\Translations::getInstance();
        switch ($month) {
            case 1:
                return $translator->get('certificatePdfMonth1');
            case 2:
                return $translator->get('certificatePdfMonth2');
            case 3:
                return $translator->get('certificatePdfMonth3');
            case 4:
                return $translator->get('certificatePdfMonth4');
            case 5:
                return $translator->get('certificatePdfMonth5');
            case 6:
                return $translator->get('certificatePdfMonth6');
            case 7:
                return $translator->get('certificatePdfMonth7');
            case 8:
                return $translator->get('certificatePdfMonth8');
            case 9:
                return $translator->get('certificatePdfMonth9');
            case 10:
                return $translator->get('certificatePdfMonth10');
            case 11:
                return $translator->get('certificatePdfMonth11');
            default:
                return $translator->get('certificatePdfMonth12');
        }
    }

    private static function createCertificatePdf(
        string $title,
        string $identityName,
        string $description,
        int $date
    ): string {
        $translator = \OmegaUp\Translations::getInstance();
        $pdf = new FPDI('L');

        $pdf->setSourceFile('/opt/omegaup/stuff/CertificateTemplate.pdf');
        $templateId = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($templateId);
        $pdf->SetAutoPageBreak(false);

        $x = 50;
        $y = 0;
        $width = 215;
        $height = 15;
        $border = 0;
        $ln = 1;
        $center = 'C';
        $bold = 'B';
        $italic = 'I';

        $pdf->SetFont('Arial', $italic, 14);
        $pdf->SetTextColor(153, 153, 153);

        $y = 41;
        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            utf8_decode(
                $translator->get(
                    'certificatePdfHeader'
                )
            ),
            $border,
            $ln,
            $center
        );

        $y = 148;
        $pdf->SetXY($x, $y);
        $day = intval(date('j', $date));
        $month = intval(date('n', $date));
        $year = intval(date('o', $date));
        $pdf->Cell(
            $width,
            $height,
            utf8_decode(
                sprintf(
                    $translator->get('certificatePdfPlaceAndDate'),
                    $day,
                    self::getMonthName($month),
                    $year
                )
            ),
            $border,
            $ln,
            $center
        );

        $y = 197;
        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            utf8_decode(
                $translator->get(
                    'certificatePdfDirector'
                )
            ),
            $border,
            $ln,
            $center
        );

        $pdf->SetFont('', $bold, 39);
        $pdf->SetTextColor(0, 0, 0);

        $y = 76;
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $height, $title, $border, $ln, $center);

        $pdf->SetFont('', $bold, 21);

        $y = 109;
        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            $identityName,
            $border,
            $ln,
            $center
        );

        $pdf->SetFont('', '', 18);

        $y = 57;
        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            utf8_decode(
                $translator->get(
                    'certificatePdfGrantsRecognition'
                )
            ),
            $border,
            $ln,
            $center
        );

        $y = 92;
        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            utf8_decode(
                $translator->get(
                    'certificatePdfPerson'
                )
            ),
            $border,
            $ln,
            $center
        );

        $y = 132;
        $height = 10;
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(
            $width,
            $height,
            $description,
            $border,
            $center
        );

        return $pdf->Output('', 'S');
    }

    private static function getPlaceSuffix(int $n): string {
        $translator = \OmegaUp\Translations::getInstance();
        if ($n >= 11 && $n <= 13) {
            return $translator->get('certificatePdfContestPlace0');
        }
        if (($n % 10) == 1) {
            return $translator->get('certificatePdfContestPlace1');
        }
        if (($n % 10) == 2) {
            return $translator->get('certificatePdfContestPlace2');
        }
        if (($n % 10) == 3) {
            return $translator->get('certificatePdfContestPlace3');
        }
        return $translator->get('certificatePdfContestPlace0');
    }

    private static function getContestCertificate(string $verification_code): string {
        $certificateData = \OmegaUp\DAO\Certificates::getContestCertificateByVerificationCode(
            $verification_code
        );

        if (is_null($certificateData)) {
            return '';
        }

        $translator = \OmegaUp\Translations::getInstance();
        $placeNumber = intval($certificateData['contest_place']);
        $title = utf8_decode(
            $placeNumber
            . self::getPlaceSuffix($placeNumber)
            . $translator->get(
                'certificatePdfContestPlace'
            )
        );
        $identityName = utf8_decode($certificateData['identity_name']);
        $description = utf8_decode(
            sprintf(
                $translator->get('certificatePdfContestDescription'),
                $certificateData['contest_title']
            )
        );
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
