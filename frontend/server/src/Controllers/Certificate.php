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

    private static function printCertificateHeader(FPDI $pdf): void {
        $translator = \OmegaUp\Translations::getInstance();

        $x = 50;
        $y = 41;
        $width = 215;
        $height = 15;
        $border = 0;
        $ln = 1;
        $center = 'C';

        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfHeader')
            ),
            $border,
            $ln,
            $center
        );
    }

    private static function printCertificatePlaceAndDate(
        FPDI $pdf,
        int $date
    ): void {
        $translator = \OmegaUp\Translations::getInstance();

        $x = 50;
        $y = 148;
        $width = 215;
        $height = 15;
        $border = 0;
        $ln = 1;
        $center = 'C';

        $pdf->SetXY($x, $y);
        $day = intval(date('j', $date));
        $month = intval(date('n', $date));
        $year = intval(date('o', $date));
        $pdf->Cell(
            $width,
            $height,
            \OmegaUp\ApiUtils::formatString(
                $translator->get('certificatePdfPlaceAndDate'),
                [
                    'month' => self::getMonthName($month),
                    'day' => $day,
                    'year' => $year,
                ],
                convertUTF8ToISO: true
            ),
            $border,
            $ln,
            $center
        );
    }

    private static function printCertificateDirector(FPDI $pdf): void {
        $translator = \OmegaUp\Translations::getInstance();

        $x = 50;
        $y = 197;
        $width = 215;
        $height = 15;
        $border = 0;
        $ln = 1;
        $center = 'C';

        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfDirector')
            ),
            $border,
            $ln,
            $center
        );
    }

    private static function printCertificateTitle(
        FPDI $pdf,
        string $title
    ): void {
        $x = 50;
        $y = 76;
        $width = 215;
        $height = 15;
        $border = 0;
        $ln = 1;
        $center = 'C';

        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $height, $title, $border, $ln, $center);
    }

    private static function printCertificateName(
        FPDI $pdf,
        string $identityName
    ): void {
        $x = 50;
        $y = 109;
        $width = 215;
        $height = 15;
        $border = 0;
        $ln = 1;
        $center = 'C';

        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            $identityName,
            $border,
            $ln,
            $center
        );
    }

    private static function printCertificateGrantsRecognition(FPDI $pdf): void {
        $translator = \OmegaUp\Translations::getInstance();

        $x = 50;
        $y = 57;
        $width = 215;
        $height = 15;
        $border = 0;
        $ln = 1;
        $center = 'C';

        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfGrantsRecognition')
            ),
            $border,
            $ln,
            $center
        );
    }

    private static function printCertificatePerson(FPDI $pdf): void {
        $translator = \OmegaUp\Translations::getInstance();

        $x = 50;
        $y = 92;
        $width = 215;
        $height = 15;
        $border = 0;
        $ln = 1;
        $center = 'C';

        $pdf->SetXY($x, $y);
        $pdf->Cell(
            $width,
            $height,
            \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfPerson')
            ),
            $border,
            $ln,
            $center
        );
    }

    private static function printCertificateDescription(
        FPDI $pdf,
        string $description
    ): void {
        $x = 50;
        $y = 132;
        $width = 215;
        $height = 10;
        $border = 0;
        $center = 'C';

        $pdf->SetXY($x, $y);
        $pdf->MultiCell(
            $width,
            $height,
            $description,
            $border,
            $center
        );
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
        $pdf->AddPage();
        $pdf->useTemplate($templateId);
        $pdf->SetAutoPageBreak(false);

        $pdf->SetFont('Arial', 'I', 14);
        $pdf->SetTextColor(153, 153, 153);
        self::printCertificateHeader($pdf);
        self::printCertificatePlaceAndDate($pdf, $date);
        self::printCertificateDirector($pdf);
        $pdf->SetFont('', 'B', 39);
        $pdf->SetTextColor(0, 0, 0);
        self::printCertificateTitle($pdf, $title);
        $pdf->SetFont('', 'B', 21);
        self::printCertificateName($pdf, $identityName);
        $pdf->SetFont('', '', 18);
        self::printCertificateGrantsRecognition($pdf);
        self::printCertificatePerson($pdf);
        self::printCertificateDescription($pdf, $description);

        return base64_encode($pdf->Output('', 'S'));
    }

    private static function getPlaceSuffix(int $n): string {
        $translator = \OmegaUp\Translations::getInstance();
        if ($n >= 11 && $n <= 13) {
            return $translator->get('certificatePdfContestPlaceTh');
        }
        if (($n % 10) == 1) {
            return $translator->get('certificatePdfContestPlaceSt');
        }
        if (($n % 10) == 2) {
            return $translator->get('certificatePdfContestPlaceNd');
        }
        if (($n % 10) == 3) {
            return $translator->get('certificatePdfContestPlaceRd');
        }
        return $translator->get('certificatePdfContestPlaceTh');
    }

    private static function getContestCertificate(string $verificationCode): ?string {
        $certificateData = \OmegaUp\DAO\Certificates::getContestCertificateByVerificationCode(
            $verificationCode
        );

        if (is_null($certificateData)) {
            return null;
        }

        $translator = \OmegaUp\Translations::getInstance();
        if (!is_null($certificateData['contest_place'])) {
            $placeNumber = intval($certificateData['contest_place']);
            $title = \OmegaUp\ApiUtils::convertUTFToISO(
                $placeNumber
                . self::getPlaceSuffix($placeNumber)
            );
        } else {
            $title = \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfContestParticipation')
            );
        }
        $identityName = \OmegaUp\ApiUtils::convertUTFToISO(
            $certificateData['identity_name']
        );
        $description = \OmegaUp\ApiUtils::formatString(
            $translator->get('certificatePdfContestDescription'),
            [
                'contest_title' => $certificateData['contest_title'],
            ],
            convertUTF8ToISO: true
        );
        $date = $certificateData['timestamp']->time;

        return self::createCertificatePdf(
            $title,
            $identityName,
            $description,
            $date
        );
    }

    private static function getCourseCertificate(string $verificationCode): ?string {
        $certificateData = \OmegaUp\DAO\Certificates::getCourseCertificateByVerificationCode(
            $verificationCode
        );

        if (is_null($certificateData)) {
            return null;
        }

        $translator = \OmegaUp\Translations::getInstance();
        $title = \OmegaUp\ApiUtils::convertUTFToISO(
            $translator->get('certificatePdfCourseTitle')
        );
        $identityName = \OmegaUp\ApiUtils::convertUTFToISO(
            $certificateData['identity_name']
        );
        $description = \OmegaUp\ApiUtils::formatString(
            $translator->get('certificatePdfCourseDescription'),
            [
                'course_name' => $certificateData['course_name'],
            ],
            convertUTF8ToISO: true
        );
        $date = $certificateData['timestamp']->time;

        return self::createCertificatePdf(
            $title,
            $identityName,
            $description,
            $date
        );
    }

    private static function getCoderOfTheMonthCertificate(
        string $verificationCode,
        bool $isFemaleCategory
    ): ?string {
        $certificateData = \OmegaUp\DAO\Certificates::getCoderOfTheMonthCertificateByVerificationCode(
            $verificationCode
        );

        if (is_null($certificateData)) {
            return null;
        }

        $translator = \OmegaUp\Translations::getInstance();
        if ($isFemaleCategory) {
            $title = \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfCoderOfTheMonthFemaleTitle')
            );
        } else {
            $title = \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfCoderOfTheMonthTitle')
            );
        }
        $identityName = \OmegaUp\ApiUtils::convertUTFToISO(
            $certificateData['identity_name']
        );
        $date = $certificateData['timestamp']->time;
        $month = intval(date('n', $date));
        $description = \OmegaUp\ApiUtils::formatString(
            $translator->get('certificatePdfCoderOfTheMonthDescription'),
            [
                'month_name' => self::getMonthName($month - 1),
            ],
            convertUTF8ToISO: true
        );

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
     * @return array{certificate: string|null}
     *
     * @omegaup-request-param string $verification_code
     */
    public static function apiGetCertificatePdf(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $verificationCode = $r->ensureString('verification_code');
        $type = \OmegaUp\DAO\Certificates::getCertificateTypeByVerificationCode(
            $verificationCode
        );

        if ($type === 'contest') {
            return [
                'certificate' => self::getContestCertificate(
                    $verificationCode
                ),
            ];
        }
        if ($type === 'course') {
            return [
                'certificate' => self::getCourseCertificate(
                    $verificationCode
                ),
            ];
        }
        if ($type === 'coder_of_the_month' || $type === 'coder_of_the_month_female') {
            return [
                'certificate' => self::getCoderOfTheMonthCertificate(
                    $verificationCode,
                    $type === 'coder_of_the_month_female'
                ),
            ];
        }
        return [
            'certificate' => null,
        ];
    }

    /**
     * API to validate a certificate
     *
     * @return array{valid: bool}
     *
     * @omegaup-request-param string $verification_code
     */
    public static function apiValidateCertificate(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $verificationCode = $r->ensureString('verification_code');
        $isValid = boolval(\OmegaUp\DAO\Certificates::isValid(
            $verificationCode
        ));

        return [
            'valid' => $isValid,
        ];
    }
}
