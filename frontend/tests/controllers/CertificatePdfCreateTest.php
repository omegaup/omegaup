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
     * Test to check that a place suffix of a contest is correct
     */
    public function testGetPlaceSuffix() {
        $translator = \OmegaUp\Translations::getInstance();

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(1);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceSt'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(2);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceNd'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(3);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceRd'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(11);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceTh'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(12);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceTh'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(13);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceTh'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(91);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceSt'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(92);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceNd'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(93);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceRd'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(4);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceTh'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(50);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceTh'
            ),
            $placeSuffix
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(98);
        $this->assertSame(
            $translator->get(
                'certificatePdfContestPlaceTh'
            ),
            $placeSuffix
        );
    }
}
