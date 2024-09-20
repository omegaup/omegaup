<?php

class GenerateCertificatesTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Check if user has certificate generator role
     */
    public function testCertificateGeneratorRole() {
        //create users
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        //logins
        $loginAdmin = self::login($admin);

        //add role certificate generator to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginAdmin->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        //add role mentor to identity2 user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginAdmin->auth_token,
            'username' => $identity2->username,
            'role' => 'Mentor'
        ]));

        //check that identity is certificate generator
        $this->assertTrue(
            \OmegaUp\Authorization::isCertificateGenerator(
                $identity
            )
        );

        //check that identity2 isn't certificate generator
        $this->assertFalse(
            \OmegaUp\Authorization::isCertificateGenerator(
                $identity2
            )
        );
    }

    /**
     * Create a course as a user that isn't a certificate generator
     * where minimum progress for certificate is not available
     */
    public function testCreateCourseWithoutMinimumProgressForCertificateValue() {
        //create user
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        //add role mentor to identity user
        $loginAdmin = self::login($admin);
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginAdmin->auth_token,
            'username' => $identity->username,
            'role' => 'Mentor'
        ]));

        $alias = \OmegaUp\Test\Utils::createRandomString();

        // create a course using the new field minimum_progress_for_certificate
        $loginIdentity = self::login($identity);
        \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $alias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'minimum_progress_for_certificate' => 100
        ]));

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        //check that minimum progress for certificate is null
        $this->assertNull($course->minimum_progress_for_certificate);
    }

    /**
     * Create a course as a user that is certificate generator
     * where minimum progress for certificate is now available
     */
    public function testCreateCourseWithMinimumProgressForCertificateValue() {
        //create user
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        //add role certificate generator to identity user
        $loginAdmin = self::login($admin);
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginAdmin->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        $alias = \OmegaUp\Test\Utils::createRandomString();

        // login normal users
        $loginIdentity = self::login($identity);

        // create a course using the new field minimum_progress_for_certificate
        \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $alias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'minimum_progress_for_certificate' => 100
        ]));

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        $this->assertSame($course->minimum_progress_for_certificate, 100);
    }

    /**
     * Update a course as a user that isn't certificate generator
     * where minimum progress for certificate isn't available
     */
    public function testUpdateCourseWithoutMinimumProgressForCertificateValue() {
        //create user
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        //add role mentor to identity2 user
        $loginAdmin = self::login($admin);
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginAdmin->auth_token,
            'username' => $identity->username,
            'role' => 'Mentor'
        ]));

        $alias = \OmegaUp\Test\Utils::createRandomString();

        // create a course using the new field minimum_progress_for_certificate
        $loginIdentity = self::login($identity);
        \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $alias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'minimum_progress_for_certificate' => 100
        ]));

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        //check that course the new field is null
        $this->assertNull($course->minimum_progress_for_certificate);

        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'name' => $course->name,
            'alias' => $course->alias,
            'description' => $course->description,
            'objective' => \OmegaUp\Test\Utils::createRandomString(),
            'minimum_progress_for_certificate' => 89
        ]));

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        //check that the new field is null yet
        $this->assertNull($course->minimum_progress_for_certificate);
    }

    /**
     * Update a course as a user that is certificate generator
     * where minimum progress for certificate is now available
     */
    public function testUpdateCourseWithMinimumProgressForCertificateValue() {
        //create user
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        //add role as certificate generator
        $loginAdmin = self::login($admin);
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginAdmin->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        $alias = \OmegaUp\Test\Utils::createRandomString();

        // create a course using the new field minimum_progress_for_certificate
        $loginIdentity = self::login($identity);
        \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $alias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'minimum_progress_for_certificate' => 100
        ]));

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        //check that course the new field is null
        $this->assertEquals($course->minimum_progress_for_certificate, 100);

        // update course modifying the new field as certificate generator
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'name' => $course->name,
            'alias' => $course->alias,
            'description' => $course->description,
            'objective' => \OmegaUp\Test\Utils::createRandomString(),
            'minimum_progress_for_certificate' => 89
        ]));

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        //check that the new field was updated
        $this->assertEquals($course->minimum_progress_for_certificate, 89);
    }

    /**
     * Obtain a user's certificates using his identity ID, also try
     * to obtain this user's certificates with another logged user
     */
    public function testGetUserCertificates() {
        // Create contest with 2 hours and a window length 30 of minutes
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create a contestant
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity1);

        // Create a certificate
        \OmegaUp\DAO\Certificates::create(new \OmegaUp\DAO\VO\Certificates([
            'identity_id' => $identity1->identity_id,
            'timestamp' => '2023-09-04',
            'certificate_type' => 'contest',
            'contest_id' => $contestData['contest']->contest_id,
            'verification_code' => 'oP8a97pL5k'
        ]));

        //login
        $loginIdentity = self::login($identity1);

        //check the certificate data
        $certificates = \OmegaUp\Controllers\Certificate::apiGetUserCertificates(
            new \OmegaUp\Request([
                'auth_token' => $loginIdentity->auth_token,
                'user_id' => $identity1->user_id,
            ])
        )['certificates'];

        $this->assertCount(1, $certificates);
        $this->assertSame('contest', $certificates[0]['certificate_type']);
        $this->assertSame(
            $contestData['contest']->title,
            $certificates[0]['name']
        );

        //login with another account
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $loginIdentity = self::login($identity2);

        //try to get the certificates of identity1
        try {
            \OmegaUp\Controllers\Certificate::apiGetUserCertificates(
                new \OmegaUp\Request([
                    'auth_token' => $loginIdentity->auth_token,
                    'user_id' => $identity1->user_id
                ])
            );

            $this->fail(
                'Should not have been able to get the user certificates'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }
}
