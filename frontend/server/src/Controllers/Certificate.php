<?php

namespace OmegaUp\Controllers;

use setasign\Fpdi\Fpdi;
/**
 * CertificateController
 *
 * @psalm-type CertificateDetailsPayload=array{uuid: string}
 * @psalm-type CertificateListItem=array{certificate_type: string, date: \OmegaUp\Timestamp, name: null|string, verification_code: string}
 * @psalm-type CertificateListMinePayload=array{certificates: list<CertificateListItem>}
 */
class Certificate extends \OmegaUp\Controllers\Controller {
    // General certificate PDF constants
    const CERTIFICATE_PDF_BORDER = 1;
    const CERTIFICATE_PDF_LN = 1;
    const CERTIFICATE_PDF_ALIGN_CENTER = 'C';
    const CERTIFICATE_PDF_ALIGN_RIGHT = 'R';
    const CERTIFICATE_PDF_CENTERED_WIDTH = 215;
    const CERTIFICATE_PDF_RIGHT_ALIGNED_WIDTH = 80;
    const CERTIFICATE_PDF_HEIGHT_SMALL = 5;
    const CERTIFICATE_PDF_HEIGHT_MEDIUM = 10;
    const CERTIFICATE_PDF_HEIGHT_BIG = 15;
    const CERTIFICATE_PDF_CENTERED_X = 50;
    const CERTIFICATE_PDF_RIGHT_ALIGNED_X = 214;

    // Constants of Y for certificate PDF
    const CERTIFICATE_PDF_HEADER_Y = 43;
    const CERTIFICATE_PDF_PLACE_AND_DATE_Y = 150;
    const CERTIFICATE_PDF_DIRECTOR_Y = 199;
    const CERTIFICATE_PDF_TITLE_Y = 76;
    const CERTIFICATE_PDF_NAME_Y = 109;
    const CERTIFICATE_PDF_GRANTS_RECOGNITION_Y = 60;
    const CERTIFICATE_PDF_PERSON_Y = 94;
    const CERTIFICATE_PDF_DESCRIPTION_Y = 132;
    const CERTIFICATE_PDF_VERIFICATION_CODE_Y = 192;
    const CERTIFICATE_PDF_VERIFICATION_LINK_Y = 197;

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
     * @return array{templateProperties: array{payload: CertificateListMinePayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getCertificateListMineForTypeScript(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $certificates = [];
        if (!is_null($r->identity->user_id)) {
            $certificates = self::getUserCertificates(
                $r->identity,
                $r->identity->user_id
            );
        }
        return [
            'templateProperties' => [
                'payload' => [
                    'certificates' => $certificates,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleMyDiplomas'
                ),
            ],
            'entrypoint' => 'certificate_mine',
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

        $pdf->SetXY(
            self::CERTIFICATE_PDF_CENTERED_X,
            self::CERTIFICATE_PDF_HEADER_Y
        );
        $pdf->Cell(
            self::CERTIFICATE_PDF_CENTERED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_MEDIUM,
            \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfHeader')
            ),
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_LN,
            self::CERTIFICATE_PDF_ALIGN_CENTER
        );
    }

    private static function printCertificatePlaceAndDate(
        FPDI $pdf,
        int $date
    ): void {
        $translator = \OmegaUp\Translations::getInstance();

        $pdf->SetXY(
            self::CERTIFICATE_PDF_CENTERED_X,
            self::CERTIFICATE_PDF_PLACE_AND_DATE_Y
        );
        $day = intval(date('j', $date));
        $month = intval(date('n', $date));
        $year = intval(date('o', $date));
        $pdf->Cell(
            self::CERTIFICATE_PDF_CENTERED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_MEDIUM,
            \OmegaUp\ApiUtils::formatString(
                $translator->get('certificatePdfPlaceAndDate'),
                [
                    'month' => self::getMonthName($month),
                    'day' => $day,
                    'year' => $year,
                ],
                convertUTF8ToISO: true
            ),
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_LN,
            self::CERTIFICATE_PDF_ALIGN_CENTER
        );
    }

    private static function printCertificateDirector(FPDI $pdf): void {
        $translator = \OmegaUp\Translations::getInstance();

        $pdf->SetXY(
            self::CERTIFICATE_PDF_CENTERED_X,
            self::CERTIFICATE_PDF_DIRECTOR_Y
        );
        $pdf->Cell(
            self::CERTIFICATE_PDF_CENTERED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_MEDIUM,
            \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfDirector')
            ),
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_LN,
            self::CERTIFICATE_PDF_ALIGN_CENTER
        );
    }

    private static function printCertificateTitle(
        FPDI $pdf,
        string $title
    ): void {
        $pdf->SetXY(
            self::CERTIFICATE_PDF_CENTERED_X,
            self::CERTIFICATE_PDF_TITLE_Y
        );
        $pdf->Cell(
            self::CERTIFICATE_PDF_CENTERED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_BIG,
            $title,
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_LN,
            self::CERTIFICATE_PDF_ALIGN_CENTER
        );
    }

    private static function printCertificateName(
        FPDI $pdf,
        string $identityName
    ): void {
        $pdf->SetXY(
            self::CERTIFICATE_PDF_CENTERED_X,
            self::CERTIFICATE_PDF_NAME_Y
        );
        $pdf->Cell(
            self::CERTIFICATE_PDF_CENTERED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_BIG,
            $identityName,
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_LN,
            self::CERTIFICATE_PDF_ALIGN_CENTER
        );
    }

    private static function printCertificateGrantsRecognition(FPDI $pdf): void {
        $translator = \OmegaUp\Translations::getInstance();

        $pdf->SetXY(
            self::CERTIFICATE_PDF_CENTERED_X,
            self::CERTIFICATE_PDF_GRANTS_RECOGNITION_Y
        );
        $pdf->Cell(
            self::CERTIFICATE_PDF_CENTERED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_MEDIUM,
            \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfGrantsRecognition')
            ),
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_LN,
            self::CERTIFICATE_PDF_ALIGN_CENTER
        );
    }

    private static function printCertificatePerson(FPDI $pdf): void {
        $translator = \OmegaUp\Translations::getInstance();

        $pdf->SetXY(
            self::CERTIFICATE_PDF_CENTERED_X,
            self::CERTIFICATE_PDF_PERSON_Y
        );
        $pdf->Cell(
            self::CERTIFICATE_PDF_CENTERED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_MEDIUM,
            \OmegaUp\ApiUtils::convertUTFToISO(
                $translator->get('certificatePdfPerson')
            ),
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_LN,
            self::CERTIFICATE_PDF_ALIGN_CENTER
        );
    }

    private static function printCertificateDescription(
        FPDI $pdf,
        string $description
    ): void {
        $pdf->SetXY(
            self::CERTIFICATE_PDF_CENTERED_X,
            self::CERTIFICATE_PDF_DESCRIPTION_Y
        );
        $pdf->MultiCell(
            self::CERTIFICATE_PDF_CENTERED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_MEDIUM,
            $description,
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_ALIGN_CENTER
        );
    }

    private static function printCertificateVerificationCode(
        FPDI $pdf,
        string $verificationCode
    ): void {
        $translator = \OmegaUp\Translations::getInstance();

        $pdf->SetXY(
            self::CERTIFICATE_PDF_RIGHT_ALIGNED_X,
            self::CERTIFICATE_PDF_VERIFICATION_CODE_Y
        );
        $pdf->Cell(
            self::CERTIFICATE_PDF_RIGHT_ALIGNED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_SMALL,
            \OmegaUp\ApiUtils::formatString(
                $translator->get('certificatePdfVerificationCode'),
                [
                    'verification_code' => $verificationCode,
                ],
                convertUTF8ToISO: true
            ),
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_LN,
            self::CERTIFICATE_PDF_ALIGN_RIGHT
        );
    }

    private static function printCertificateVerificationLink(
        FPDI $pdf,
        string $verificationCode
    ): void {
        $translator = \OmegaUp\Translations::getInstance();

        $pdf->SetXY(
            self::CERTIFICATE_PDF_RIGHT_ALIGNED_X,
            self::CERTIFICATE_PDF_VERIFICATION_LINK_Y
        );
        $pdf->MultiCell(
            self::CERTIFICATE_PDF_RIGHT_ALIGNED_WIDTH,
            self::CERTIFICATE_PDF_HEIGHT_SMALL,
            \OmegaUp\ApiUtils::formatString(
                $translator->get('certificatePdfVerificationLink'),
                [
                    'verification_code' => $verificationCode,
                ],
                convertUTF8ToISO: true
            ),
            self::CERTIFICATE_PDF_BORDER,
            self::CERTIFICATE_PDF_ALIGN_RIGHT
        );
    }

    private static function createCertificatePdf(
        string $verificationCode,
        string $title,
        string $identityName,
        string $description,
        int $date
    ): string {
        $pdf = new FPDI('L');
        $pdf->setSourceFile(
            dirname(__DIR__, 4) . '/stuff/CertificateTemplate.pdf'
        );
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
        $pdf->SetFont('', '', 10);
        self::printCertificateVerificationCode($pdf, $verificationCode);
        self::printCertificateVerificationLink($pdf, $verificationCode);

        return base64_encode($pdf->Output('', 'S'));
    }

    public static function getPlaceSuffix(int $n): string {
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
            $verificationCode,
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
            $verificationCode,
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
            $verificationCode,
            $title,
            $identityName,
            $description,
            $date
        );
    }

    public static function getCertificatePdf(string $verificationCode): ?string {
        $type = \OmegaUp\DAO\Certificates::getCertificateTypeByVerificationCode(
            $verificationCode
        );

        if ($type === 'contest') {
            return self::getContestCertificate($verificationCode);
        }
        if ($type === 'course') {
            return self::getCourseCertificate($verificationCode);
        }
        if ($type === 'coder_of_the_month' || $type === 'coder_of_the_month_female') {
            return self::getCoderOfTheMonthCertificate(
                $verificationCode,
                isFemaleCategory: $type === 'coder_of_the_month_female'
            );
        }
        return null;
    }

    /**
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return list<CertificateListItem>
     */
    private static function getUserCertificates(
        \OmegaUp\DAO\VO\Identities $identity,
        int $userId
    ): array {
        if (
            $identity->user_id !== $userId &&
            !\OmegaUp\Authorization::isSystemAdmin($identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        return \OmegaUp\DAO\Certificates::getUserCertificates(
            $userId
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

        return [
            'certificate' => self::getCertificatePdf(
                $r->ensureString('verification_code')
            ),
        ];
    }

    /**
     * Get all the certificates belonging to a user
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{certificates: list<CertificateListItem>}
     *
     * @omegaup-request-param int|null $user_id
     */
    public static function apiGetUserCertificates(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();

        return [
            'certificates' => self::getUserCertificates(
                $r->identity,
                $r->ensureInt('user_id')
            ),
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
