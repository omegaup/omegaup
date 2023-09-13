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
        $r = new \OmegaUp\Request(['verification_code' => 'D89lJ2aOZ3',]);

        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf($r);

        $this->assertEmpty($response['certificate']);

        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'contest',
            'contest_id' => $contestData['contest']->contest_id,
            'verification_code' => '45KoPi9aM3'
        ]));

        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf($r);

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
        $this->assertSame($certificateType, 'contest');

        $certificateData = \OmegaUp\DAO\Certificates::getContestCertificateByVerificationCode(
            '5OpsU8zX80'
        );
        $this->assertSame(
            $certificateData['contest_title'],
            $contestData['contest']->title
        );
        $this->assertSame($certificateData['identity_name'], $identity->name);
        $this->assertNull($certificateData['contest_place']);

        $r = new \OmegaUp\Request(['verification_code' => '5OpsU8zX80',]);
        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf($r);
        $pdf = $response['certificate'];
        $this->assertNotEmpty($pdf);
    }

    /**
     * Test for creating a certificate PDF of a course
     */
    public function testCreateCertificatePdfCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'course',
            'course_id' => 1,
            'verification_code' => '9lP5j0aLx6'
        ]));

        $certificateType = \OmegaUp\DAO\Certificates::getCertificateTypeByVerificationCode(
            '9lP5j0aLx6'
        );
        $this->assertSame($certificateType, 'course');

        $certificateData = \OmegaUp\DAO\Certificates::getContestCertificateByVerificationCode(
            '9lP5j0aLx6'
        );
        $this->assertSame(
            $certificateData['course_name'],
            $courseData['course_name']
        );
        $this->assertSame($certificateData['identity_name'], $identity->name);

        $r = new \OmegaUp\Request(['verification_code' => '9lP5j0aLx6',]);
        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf($r);
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
        $this->assertSame($certificateType, 'coder_of_the_month');

        $certificateData = \OmegaUp\DAO\Certificates::getContestCertificateByVerificationCode(
            'Kp8L30nJQ3'
        );
        $this->assertSame($certificateData['identity_name'], $identity->name);

        $r = new \OmegaUp\Request(['verification_code' => 'Kp8L30nJQ3',]);
        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf($r);
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
        $this->assertSame($certificateType, 'coder_of_the_month_female');

        $certificateData = \OmegaUp\DAO\Certificates::getContestCertificateByVerificationCode(
            'ao8A22kUmg'
        );
        $this->assertSame($certificateData['identity_name'], $identity->name);

        $r = new \OmegaUp\Request(['verification_code' => 'ao8A22kUmg',]);
        $response = \OmegaUp\Controllers\Certificate::apiGetCertificatePdf($r);
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
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceSt'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(2);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceNd'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(3);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceRd'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(11);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceTh'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(12);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceTh'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(13);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceTh'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(91);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceSt'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(92);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceNd'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(93);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceRd'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(4);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceTh'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(50);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceTh'
            )
        );

        $placeSuffix = \OmegaUp\Controllers\Certificate::getPlaceSuffix(98);
        $this->assertSame(
            $placeSuffix,
            $translator->get(
                'certificatePdfContestPlaceTh'
            )
        );
    }
}
