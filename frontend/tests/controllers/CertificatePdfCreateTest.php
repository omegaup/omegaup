<?php
/**
 * Tests for apiGetCertificatePdf in CertificateController
 */

class CertificatePdfCreateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test for creating a certificate PDF with a verification_code
     * that doesn't exist
     */
    public function testCreateCertificatePdfWithInvalidVerificationCode() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'contest',
            'contest_id' => $contestData['contest']->contest_id,
            'verification_code' => '45KoPi9aM3'
        ]));

        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf(
            new \OmegaUp\Request(['verification_code' => 'D89lJ2aOZ3',])
        );

        $this->assertEmpty($response['certificate']);
    }

    /**
     * Test for creating a certificate PDF of a contest
     */
    public function testCreateCertificatePdfContest() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'contest',
            'contest_id' => $contestData['contest']->contest_id,
            'verification_code' => '5OpsU8zX80'
        ]));

        $certificateType = \OmegaUp\DAO\Certificates::getCertificateTypeByVerificationCode(
            '5OpsU8zX80'
        );
        $this->assertSame('contest', $certificateType);

        $certificateData = \OmegaUp\DAO\Certificates::getContestCertificateByVerificationCode(
            '5OpsU8zX80'
        );
        $this->assertSame(
            $contestData['contest']->title,
            $certificateData['contest_title']
        );
        $this->assertSame($identity->name, $certificateData['identity_name']);
        $this->assertNull($certificateData['contest_place']);

        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf(
            new \OmegaUp\Request(['verification_code' => '5OpsU8zX80',])
        );
        $pdf = $response['certificate'];
        $this->assertNotEmpty($pdf);
    }

    /**
     * Test for creating a certificate PDF of a course
     */
    public function testCreateCertificatePdfCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'course',
            'course_id' => $course->course_id,
            'verification_code' => '9lP5j0aLx6'
        ]));

        $certificateType = \OmegaUp\DAO\Certificates::getCertificateTypeByVerificationCode(
            '9lP5j0aLx6'
        );
        $this->assertSame('course', $certificateType);

        $certificateData = \OmegaUp\DAO\Certificates::getCourseCertificateByVerificationCode(
            '9lP5j0aLx6'
        );
        $this->assertSame(
            $courseData['course_name'],
            $certificateData['course_name']
        );
        $this->assertSame($identity->name, $certificateData['identity_name']);

        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf(
            new \OmegaUp\Request(['verification_code' => '9lP5j0aLx6',])
        );
        $pdf = $response['certificate'];
        $this->assertNotEmpty($pdf);
    }

    /**
     * Test for creating a certificate PDF of the coder of the month
     */
    public function testCreateCertificatePdfCoderOfTheMonth() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'coder_of_the_month',
            'verification_code' => 'Kp8L30nJQ3'
        ]));

        $certificateType = \OmegaUp\DAO\Certificates::getCertificateTypeByVerificationCode(
            'Kp8L30nJQ3'
        );
        $this->assertSame('coder_of_the_month', $certificateType);

        $certificateData = \OmegaUp\DAO\Certificates::getCoderOfTheMonthCertificateByVerificationCode(
            'Kp8L30nJQ3'
        );
        $this->assertSame($identity->name, $certificateData['identity_name']);

        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf(
            new \OmegaUp\Request(['verification_code' => 'Kp8L30nJQ3',])
        );
        $pdf = $response['certificate'];
        $this->assertNotEmpty($pdf);
    }

    /**
     * Test for creating a certificate PDF of the coder of the month
     * in the female category
     */
    public function testCreateCertificatePdfCoderOfTheMonthFemale() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'coder_of_the_month_female',
            'verification_code' => 'ao8A22kUmg'
        ]));

        $certificateType = \OmegaUp\DAO\Certificates::getCertificateTypeByVerificationCode(
            'ao8A22kUmg'
        );
        $this->assertSame('coder_of_the_month_female', $certificateType);

        $certificateData = \OmegaUp\DAO\Certificates::getCoderOfTheMonthCertificateByVerificationCode(
            'ao8A22kUmg'
        );
        $this->assertSame($identity->name, $certificateData['identity_name']);

        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf(
            new \OmegaUp\Request(['verification_code' => 'ao8A22kUmg',])
        );
        $pdf = $response['certificate'];
        $this->assertNotEmpty($pdf);
    }

    /**
     * A PHPUnit data provider for contest place suffixes.
     *
     * @return list<array{0: int, 1: string}>
     */
    public function placeSuffixProvider(): array {
        return [
            [1, 'certificatePdfContestPlaceSt'],
            [2, 'certificatePdfContestPlaceNd'],
            [3, 'certificatePdfContestPlaceRd'],
            [4, 'certificatePdfContestPlaceTh'],
            [11, 'certificatePdfContestPlaceTh'],
            [12, 'certificatePdfContestPlaceTh'],
            [13, 'certificatePdfContestPlaceTh'],
            [50, 'certificatePdfContestPlaceTh'],
            [91, 'certificatePdfContestPlaceSt'],
            [92, 'certificatePdfContestPlaceNd'],
            [93, 'certificatePdfContestPlaceRd'],
            [98, 'certificatePdfContestPlaceTh'],
            [101, 'certificatePdfContestPlaceSt'],
            [102, 'certificatePdfContestPlaceNd'],
            [103, 'certificatePdfContestPlaceRd'],
            [104, 'certificatePdfContestPlaceTh'],
            [111, 'certificatePdfContestPlaceTh'],
            [112, 'certificatePdfContestPlaceTh'],
            [113, 'certificatePdfContestPlaceTh'],
            [121, 'certificatePdfContestPlaceSt'],
            [122, 'certificatePdfContestPlaceNd'],
            [123, 'certificatePdfContestPlaceRd'],
            [200, 'certificatePdfContestPlaceTh'],
            [211, 'certificatePdfContestPlaceTh'],
            [212, 'certificatePdfContestPlaceTh'],
            [213, 'certificatePdfContestPlaceTh'],
            [1011, 'certificatePdfContestPlaceTh'],
            [1012, 'certificatePdfContestPlaceTh'],
            [1013, 'certificatePdfContestPlaceTh'],
        ];
    }

    /**
     * Test to check that a place suffix of a contest is correct
     *
     * @dataProvider placeSuffixProvider
     */
    public function testGetPlaceSuffix(
        int $place,
        string $expectedSuffixKey
    ) {
        $originalAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        $originalRequestLang = $_REQUEST['lang'] ?? null;
        try {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
            $_REQUEST['lang'] = 'en';
            $translator = \OmegaUp\Translations::getInstance(lang: 'en');
            $this->assertNotSame(
                $translator->get('certificatePdfContestPlaceSt'),
                $translator->get('certificatePdfContestPlaceTh')
            );
            $this->assertSame(
                $translator->get($expectedSuffixKey),
                \OmegaUp\Controllers\Certificate::getPlaceSuffix(
                    $place,
                    $translator
                )
            );
            $this->assertSame(
                $translator->get($expectedSuffixKey),
                \OmegaUp\Controllers\Certificate::getPlaceSuffix($place)
            );
        } finally {
            if (is_null($originalRequestLang)) {
                unset($_REQUEST['lang']);
            } else {
                $_REQUEST['lang'] = $originalRequestLang;
            }
            if (is_null($originalAcceptLanguage)) {
                unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            } else {
                $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $originalAcceptLanguage;
            }
        }
    }
}
