<?php

class GenerateCertificatesTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Check if user has certificate generator role
     */
    public function testCertificateGeneratorRole() {
        //create users
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        //logins
        $loginIdentity = self::login($identity);
        $loginIdentity2 = self::login($identity2);

        //add role certificate generator to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        //add role mentor to identity2 user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity2->auth_token,
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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // login normal users
        $loginIdentity = self::login($identity);

        //add role mentor to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $identity->username,
            'role' => 'Mentor'
        ]));

        $alias = \OmegaUp\Test\Utils::createRandomString();

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

        //check that minimum progress for certificate is null
        $this->assertNull($course->minimum_progress_for_certificate);
    }

    /**
     * Create a course as a user that is certificate generator
     * where minimum progress for certificate is now available
     */
    public function testCreateCourseWithMinimumProgressForCertificateValue() {
        //create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // login normal users
        $loginIdentity = self::login($identity);

        //add role certificate generator to identity user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        $alias = \OmegaUp\Test\Utils::createRandomString();

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

        $this->assertEquals($course->minimum_progress_for_certificate, 100);
    }

    /**
     * Update a course as a user that isn't certificate generator
     * where minimum progress for certificate isn't available
     */
    public function testUpdateCourseWithoutMinimumProgressForCertificateValue() {
        //create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // login normal users
        $loginIdentity = self::login($identity);

        //add role mentor to identity2 user
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $identity->username,
            'role' => 'Mentor'
        ]));

        $alias = \OmegaUp\Test\Utils::createRandomString();

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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $loginIdentity = self::login($identity);

        //add role as certificate generator
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $loginIdentity->auth_token,
            'username' => $identity->username,
            'role' => 'CertificateGenerator'
        ]));

        $alias = \OmegaUp\Test\Utils::createRandomString();

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
}
