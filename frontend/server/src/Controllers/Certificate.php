<?php

namespace OmegaUp\Controllers;

use setasign\Fpdi\Fpdi;
/**
 * CertificateController
 *
 * @psalm-type CertificateDetailsPayload=array{uuid: string}
 * @psalm-type CertificateValidationPayload=array{certificate: null|string, verification_code: string, valid: bool}
 * @psalm-type CertificateListItem=array{certificate_type: string, date: \OmegaUp\Timestamp, name: null|string, verification_code: string}
 * @psalm-type CertificateListMinePayload=array{certificates: list<CertificateListItem>}
 */
class Certificate extends \OmegaUp\Controllers\Controller {
    // General certificate PDF constants
    const CERTIFICATE_PDF_BORDER = 0;
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
            $certificates = \OmegaUp\DAO\Certificates::getUserCertificates(
                $r->identity->user_id
            );
        }
        return [
            'templateProperties' => [
                'payload' => [
                    'certificates' => $certificates,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleMyCertificates'
                ),
            ],
            'entrypoint' => 'certificate_mine',
        ];
    }

    /**
     * @return array{templateProperties: array{payload: CertificateValidationPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $verification_code
     */
    public static function getValidationForTypeScript(\OmegaUp\Request $r) {
        $verificationCode = $r->ensureString('verification_code');

        return [
            'templateProperties' => [
                'payload' => [
                    'verification_code' => $verificationCode,
                    'valid' => boolval(\OmegaUp\DAO\Certificates::isValid(
                        $verificationCode
                    )),
                    'certificate' => self::getCertificatePdf($verificationCode),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCertificateValidation'
                ),
            ],
            'entrypoint' => 'certificate_validation',
        ];
    }

    private static function getMonthName(int $month): string {
        $translator = \OmegaUp\Translations::getInstance();

        return match ($month) {
            1 => $translator->get('certificatePdfMonth1'),
            2 => $translator->get('certificatePdfMonth2'),
            3 => $translator->get('certificatePdfMonth3'),
            4 => $translator->get('certificatePdfMonth4'),
            5 => $translator->get('certificatePdfMonth5'),
            6 => $translator->get('certificatePdfMonth6'),
            7 => $translator->get('certificatePdfMonth7'),
            8 => $translator->get('certificatePdfMonth8'),
            9 => $translator->get('certificatePdfMonth9'),
            10 => $translator->get('certificatePdfMonth10'),
            11 => $translator->get('certificatePdfMonth11'),
            default => $translator->get('certificatePdfMonth12'),
        };
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

        $output = $pdf->Output('', 'S');
        if (!is_string($output)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestCertificatesError'
            );
        }
        return base64_encode($output);
    }

    public static function getPlaceSuffix(int $n): string {
        $translator = \OmegaUp\Translations::getInstance();

        if ($n >= 11 && $n <= 13) {
            return $translator->get('certificatePdfContestPlaceTh');
        }

        return match ($n % 10) {
            1 => $translator->get('certificatePdfContestPlaceSt'),
            2 => $translator->get('certificatePdfContestPlaceNd'),
            3 => $translator->get('certificatePdfContestPlaceRd'),
            default => $translator->get('certificatePdfContestPlaceTh'),
        };
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

        $certificate = null;

        if ($type === 'contest') {
            $certificate = self::getContestCertificate($verificationCode);
        } elseif ($type === 'course') {
            $certificate = self::getCourseCertificate($verificationCode);
        } elseif ($type === 'coder_of_the_month' || $type === 'coder_of_the_month_female') {
            $certificate = self::getCoderOfTheMonthCertificate(
                $verificationCode,
                isFemaleCategory: $type === 'coder_of_the_month_female'
            );
        }

        return $certificate;
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
     * Generates all the certificates for a contest given its contest alias.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param int|null $certificates_cutoff
     * @omegaup-request-param string|null $contest_alias
     */
    public static function apiGenerateContestCertificates(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();

        $contestAlias = $r->ensureString('contest_alias');

        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);

        if (
            is_null($contest)
            || is_null($contest->problemset_id)
            || is_null($contest->alias)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );

        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        // check whether the logged user is a certificate generator and a contest admin
        if (
            !\OmegaUp\Authorization::isCertificateGenerator($r->identity) ||
            !\OmegaUp\Authorization::isContestAdmin($r->identity, $contest)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // check that the certificates can be generated
        if (
            $contest->certificates_status !== 'uninitiated' &&
            $contest->certificates_status !== 'retryable_error'
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestCertificatesError'
            );
        }

        // check that the contest has ended
        if ($contest->finish_time->time > \OmegaUp\Time::get()) {
            $contest->certificates_status = 'retryable_error';
            \OmegaUp\DAO\Contests::update($contest);
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestCertificatesCurrentContestError'
            );
        }

        $certificateCutoff = $r->ensureOptionalInt('certificates_cutoff');

        // add certificates_cutoff value to the contest
        if (!is_null($certificateCutoff)) {
            $contest->certificate_cutoff = $certificateCutoff;

            // update contest with the new value
            \OmegaUp\DAO\Contests::update($contest);
        }

        // get contest info
        $contestExtraInformation = \OmegaUp\DAO\Contests::getByAliasWithExtraInformation(
            $contest->alias
        );

        if (is_null($contestExtraInformation)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        // set RabbitMQ client parameters
        $routingKey = 'ContestQueue';
        $exchange = 'certificates';

        // connection to rabbitmq
        $channel = \OmegaUp\RabbitMQConnection::getInstance()->channel();

        $scoreboard = \OmegaUp\Controllers\Contest::getScoreboard(
            $contest,
            $problemset,
            $r->identity,
            $contestExtraInformation['scoreboard_url']
        );

        $ranking = array_map(
            fn ($rank) => ['username' => $rank['username'] , 'place' => $rank['place'] ?? null],
            $scoreboard['ranking']
        );

        // prepare the message
        $messageArray = [
            'certificate_cutoff' => $contestExtraInformation['certificate_cutoff'],
            'alias' => $contestExtraInformation['alias'],
            'scoreboard_url' => $contestExtraInformation['scoreboard_url'],
            'contest_id' => $contestExtraInformation['contest_id'],
            'ranking' => $ranking,
        ];
        $messageJSON = json_encode($messageArray);
        $message = new \PhpAmqpLib\Message\AMQPMessage($messageJSON);

        // send the message to RabbitMQ
        $channel->basic_publish($message, $exchange, $routingKey);
        $channel->close();

        $contest->certificates_status = 'queued';
        \OmegaUp\DAO\Contests::update($contest);

        return [
            'status' => 'ok',
        ];
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

        return [
            'valid' => boolval(\OmegaUp\DAO\Certificates::isValid(
                $r->ensureString('verification_code')
            )),
        ];
    }
}
