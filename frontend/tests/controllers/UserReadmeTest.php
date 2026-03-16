<?php
/**
 * Tests for user profile README.
 */
class UserReadmeTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Tests creating a new README for a user.
     */
    public function testCreateReadme() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $content = '# Hello\n\nI am an omegaUp user.';
        $response = \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => $content,
            ])
        );

        $this->assertSame('ok', $response['status']);

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($readme);
        $this->assertSame($content, $readme->content);
        $this->assertTrue($readme->is_visible);
        $this->assertFalse($readme->is_disabled);
        $this->assertSame(0, $readme->report_count);
    }

    /**
     * Tests updating an existing README.
     */
    public function testUpdateExistingReadme() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => 'Initial content',
            ])
        );

        $updatedContent = '# Updated content\n\nNew information.';
        $response = \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => $updatedContent,
            ])
        );

        $this->assertSame('ok', $response['status']);

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($readme);
        $this->assertSame($updatedContent, $readme->content);
    }

    /**
     * Tests that the profile includes the README when it is visible and enabled.
     */
    public function testProfileIncludesReadme() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $viewer] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $content = '## My profile\n\nI enjoy competitive programming.';
        \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => $content,
            ])
        );

        $viewerLogin = self::login($viewer);
        $response = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'auth_token' => $viewerLogin->auth_token,
                'username' => $identity->username,
            ])
        );

        $this->assertArrayHasKey('readme', $response);
        $this->assertSame($content, $response['readme']);
    }

    /**
     * Tests that the profile returns null for readme when it is disabled.
     */
    public function testProfileReadmeNullWhenDisabled() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $viewer] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => 'Content that will be disabled',
            ])
        );

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($readme);
        \OmegaUp\DAO\UserReadmes::setDisabled(
            intval($readme->readme_id),
            isDisabled: true
        );

        $viewerLogin = self::login($viewer);
        $response = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'auth_token' => $viewerLogin->auth_token,
                'username' => $identity->username,
            ])
        );

        $this->assertArrayHasKey('readme', $response);
        $this->assertNull($response['readme']);
    }

    /**
     * Tests that the profile returns null for readme when it does not exist.
     */
    public function testProfileReadmeNullWhenNotExists() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $viewer] = \OmegaUp\Test\Factories\User::createUser();

        $viewerLogin = self::login($viewer);
        $response = \OmegaUp\Controllers\User::apiProfile(
            new \OmegaUp\Request([
                'auth_token' => $viewerLogin->auth_token,
                'username' => $identity->username,
            ])
        );

        $this->assertArrayHasKey('readme', $response);
        $this->assertNull($response['readme']);
    }

    /**
     * Tests that apiSaveReadme fails if the content exceeds 10,000 characters.
     */
    public function testUpdateReadmeTooLong() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiSaveReadme(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'readme' => str_repeat('a', 10001),
                ])
            );
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterStringTooLong', $e->getMessage());
        }
    }

    /**
     * Tests reporting a README and verifying the report counter is updated.
     */
    public function testReportReadme() {
        ['user' => $targetUser, 'identity' => $targetIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $reporter] = \OmegaUp\Test\Factories\User::createUser();

        $ownerLogin = self::login($targetIdentity);
        \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $ownerLogin->auth_token,
                'readme' => 'Inappropriate content',
            ])
        );

        $reporterLogin = self::login($reporter);
        $response = \OmegaUp\Controllers\User::apiReportReadme(
            new \OmegaUp\Request([
                'auth_token' => $reporterLogin->auth_token,
                'username' => $targetIdentity->username,
            ])
        );

        $this->assertSame('ok', $response['status']);

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($targetUser->user_id)
        );
        $this->assertNotNull($readme);
        $this->assertSame(1, $readme->report_count);
        $this->assertFalse($readme->is_disabled);
    }

    /**
     * Tests that auto-disabling occurs when the report threshold is reached.
     */
    public function testAutoDisableAtReportThreshold() {
        ['user' => $targetUser, 'identity' => $targetIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $ownerLogin = self::login($targetIdentity);
        \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $ownerLogin->auth_token,
                'readme' => 'Content that will receive many reports',
            ])
        );

        $threshold = \OmegaUp\Controllers\User::README_REPORT_THRESHOLD;
        for ($i = 0; $i < $threshold; $i++) {
            ['identity' => $reporter] = \OmegaUp\Test\Factories\User::createUser();
            $reporterLogin = self::login($reporter);
            \OmegaUp\Controllers\User::apiReportReadme(
                new \OmegaUp\Request([
                    'auth_token' => $reporterLogin->auth_token,
                    'username' => $targetIdentity->username,
                ])
            );
        }

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($targetUser->user_id)
        );
        $this->assertNotNull($readme);
        $this->assertTrue($readme->is_disabled);
        $this->assertGreaterThanOrEqual($threshold, $readme->report_count);
    }

    /**
     * Tests that a user cannot report the same README twice.
     */
    public function testDuplicateReportPrevented() {
        ['identity' => $targetIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $reporter] = \OmegaUp\Test\Factories\User::createUser();

        $ownerLogin = self::login($targetIdentity);
        \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $ownerLogin->auth_token,
                'readme' => 'README to report',
            ])
        );

        $reporterLogin = self::login($reporter);
        \OmegaUp\Controllers\User::apiReportReadme(
            new \OmegaUp\Request([
                'auth_token' => $reporterLogin->auth_token,
                'username' => $targetIdentity->username,
            ])
        );

        try {
            \OmegaUp\Controllers\User::apiReportReadme(
                new \OmegaUp\Request([
                    'auth_token' => $reporterLogin->auth_token,
                    'username' => $targetIdentity->username,
                ])
            );
            $this->fail(
                'Should have thrown DuplicatedEntryInDatabaseException'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('readmeAlreadyReported', $e->getMessage());
        }
    }

    /**
     * Tests that editing a disabled README re-enables it.
     */
    public function testUpdateReadmeRestoresAfterDisable() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => 'Original content',
            ])
        );

        $readme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($readme);
        \OmegaUp\DAO\UserReadmes::setDisabled(
            intval($readme->readme_id),
            isDisabled: true
        );

        \OmegaUp\Controllers\User::apiSaveReadme(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'readme' => 'Updated content after disable',
            ])
        );

        $updatedReadme = \OmegaUp\DAO\UserReadmes::getByUserId(
            intval($user->user_id)
        );
        $this->assertNotNull($updatedReadme);
        $this->assertFalse($updatedReadme->is_disabled);
        $this->assertSame(
            'Updated content after disable',
            $updatedReadme->content
        );
    }

    /**
     * Tests that reporting a non-existent README throws NotFoundException.
     */
    public function testReportNonExistentReadme() {
        ['identity' => $targetIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $reporter] = \OmegaUp\Test\Factories\User::createUser();

        $reporterLogin = self::login($reporter);
        try {
            \OmegaUp\Controllers\User::apiReportReadme(
                new \OmegaUp\Request([
                    'auth_token' => $reporterLogin->auth_token,
                    'username' => $targetIdentity->username,
                ])
            );
            $this->fail('Should have thrown NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('resourceNotFound', $e->getMessage());
        }
    }
}
