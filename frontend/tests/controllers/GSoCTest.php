<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests for GSoC Controller
 */
class GSoCTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test listing editions (public endpoint)
     */
    public function testListEditionsSuccess() {
        // Create a test edition
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2025, true, null);

        // Call API without authentication (public endpoint)
        $response = \OmegaUp\Controllers\GSoC::apiListEditions(
            new \OmegaUp\Request()
        );

        $this->assertArrayHasKey('editions', $response);
        $this->assertIsArray($response['editions']);
        $this->assertGreaterThanOrEqual(1, count($response['editions']));

        // Verify the edition we created is in the list
        $found = false;
        foreach ($response['editions'] as $edition) {
            if ($edition['edition_id'] == $editionId) {
                $found = true;
                $this->assertSame(2025, $edition['year']);
                $this->assertTrue($edition['is_active']);
                break;
            }
        }
        $this->assertTrue($found, 'Created edition should be in the list');
    }

    /**
     * Test listing ideas (public endpoint)
     */
    public function testListIdeasSuccess() {
        // Create test data
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2025, true, null);
        $ideaId = \OmegaUp\DAO\GSoC::createIdea(
            $editionId,
            'Test Idea',
            'Test description',
            null,
            null,
            null,
            null,
            null,
            'Proposed',
            null,
            null
        );

        // Call API without authentication (public endpoint)
        $response = \OmegaUp\Controllers\GSoC::apiListIdeas(
            new \OmegaUp\Request()
        );

        $this->assertArrayHasKey('ideas', $response);
        $this->assertIsArray($response['ideas']);
        $this->assertGreaterThanOrEqual(1, count($response['ideas']));

        // Verify the idea we created is in the list
        $found = false;
        foreach ($response['ideas'] as $idea) {
            if ($idea['idea_id'] == $ideaId) {
                $found = true;
                $this->assertSame('Test Idea', $idea['title']);
                $this->assertSame('Proposed', $idea['status']);
                break;
            }
        }
        $this->assertTrue($found, 'Created idea should be in the list');
    }

    /**
     * Test filtering ideas by edition_id
     */
    public function testListIdeasFilteredByEdition() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $edition2025 = \OmegaUp\DAO\GSoC::createEdition(2025, true, null);
        $edition2024 = \OmegaUp\DAO\GSoC::createEdition(2024, true, null);

        $idea2025 = \OmegaUp\DAO\GSoC::createIdea(
            $edition2025,
            'Idea 2025',
            null,
            null,
            null,
            null,
            null,
            null,
            'Proposed',
            null,
            null
        );
        $idea2024 = \OmegaUp\DAO\GSoC::createIdea(
            $edition2024,
            'Idea 2024',
            null,
            null,
            null,
            null,
            null,
            null,
            'Proposed',
            null,
            null
        );

        // Filter by 2025 edition
        $response = \OmegaUp\Controllers\GSoC::apiListIdeas(new \OmegaUp\Request([
            'edition_id' => $edition2025,
        ]));

        $this->assertArrayHasKey('ideas', $response);
        $found2025 = false;
        $found2024 = false;
        foreach ($response['ideas'] as $idea) {
            if ($idea['idea_id'] == $idea2025) {
                $found2025 = true;
            }
            if ($idea['idea_id'] == $idea2024) {
                $found2024 = true;
            }
        }
        $this->assertTrue(
            $found2025,
            'Idea from 2025 should be in filtered results'
        );
        $this->assertFalse(
            $found2024,
            'Idea from 2024 should not be in filtered results'
        );
    }

    /**
     * Test filtering ideas by status
     */
    public function testListIdeasFilteredByStatus() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2025, true, null);
        $proposedIdea = \OmegaUp\DAO\GSoC::createIdea(
            $editionId,
            'Proposed Idea',
            null,
            null,
            null,
            null,
            null,
            null,
            'Proposed',
            null,
            null
        );
        $acceptedIdea = \OmegaUp\DAO\GSoC::createIdea(
            $editionId,
            'Accepted Idea',
            null,
            null,
            null,
            null,
            null,
            null,
            'Accepted',
            null,
            null
        );

        // Filter by Accepted status
        $response = \OmegaUp\Controllers\GSoC::apiListIdeas(new \OmegaUp\Request([
            'status' => 'Accepted',
        ]));

        $this->assertArrayHasKey('ideas', $response);
        $foundProposed = false;
        $foundAccepted = false;
        foreach ($response['ideas'] as $idea) {
            if ($idea['idea_id'] == $proposedIdea) {
                $foundProposed = true;
            }
            if ($idea['idea_id'] == $acceptedIdea) {
                $foundAccepted = true;
            }
        }
        $this->assertTrue(
            $foundAccepted,
            'Accepted idea should be in filtered results'
        );
        $this->assertFalse(
            $foundProposed,
            'Proposed idea should not be in filtered results'
        );
    }

    /**
     * Test invalid status filter
     */
    public function testListIdeasInvalidStatus() {
        try {
            \OmegaUp\Controllers\GSoC::apiListIdeas(new \OmegaUp\Request([
                'status' => 'InvalidStatus',
            ]));
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Test creating edition as admin
     */
    public function testCreateEditionAsAdmin() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $response = \OmegaUp\Controllers\GSoC::apiCreateEdition(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'year' => 2026,
            'is_active' => true,
            'application_deadline' => '2026-03-15 23:59:59',
        ]));

        $this->assertArrayHasKey('edition_id', $response);
        $this->assertGreaterThan(0, $response['edition_id']);

        // Verify in database
        $edition = \OmegaUp\DAO\GSoC::getEditionById($response['edition_id']);
        $this->assertNotNull($edition);
        $this->assertSame(2026, $edition['year']);
        $this->assertTrue($edition['is_active']);
    }

    /**
     * Test creating edition requires admin
     */
    public function testCreateEditionRequiresAdmin() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\GSoC::apiCreateEdition(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'year' => 2026,
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Test creating edition with invalid year
     */
    public function testCreateEditionInvalidYear() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        // Test year too low
        try {
            \OmegaUp\Controllers\GSoC::apiCreateEdition(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'year' => 2000,
            ]));
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }

        // Test year too high
        try {
            \OmegaUp\Controllers\GSoC::apiCreateEdition(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'year' => 2200,
            ]));
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Test creating duplicate edition
     */
    public function testCreateEditionDuplicateYear() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        // Create first edition
        \OmegaUp\Controllers\GSoC::apiCreateEdition(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'year' => 2027,
        ]));

        // Try to create duplicate
        try {
            \OmegaUp\Controllers\GSoC::apiCreateEdition(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'year' => 2027,
            ]));
            $this->fail(
                'Should have thrown DuplicatedEntryInDatabaseException'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('editionAlreadyExists', $e->getMessage());
        }
    }

    /**
     * Test creating edition with invalid deadline
     */
    public function testCreateEditionInvalidDeadline() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        try {
            \OmegaUp\Controllers\GSoC::apiCreateEdition(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'year' => 2028,
                'application_deadline' => 'invalid-date',
            ]));
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Test updating edition as admin
     */
    public function testUpdateEditionAsAdmin() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2029, false, null);

        $response = \OmegaUp\Controllers\GSoC::apiUpdateEdition(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'edition_id' => $editionId,
            'is_active' => true,
        ]));

        $this->assertArrayHasKey('updated', $response);
        $this->assertTrue($response['updated']);

        // Verify update in database
        $edition = \OmegaUp\DAO\GSoC::getEditionById($editionId);
        $this->assertTrue($edition['is_active']);
    }

    /**
     * Test updating edition requires admin
     */
    public function testUpdateEditionRequiresAdmin() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);
        $editionId = \OmegaUp\DAO\GSoC::createEdition(2030, false, null);

        try {
            \OmegaUp\Controllers\GSoC::apiUpdateEdition(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'edition_id' => $editionId,
                'is_active' => true,
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Test updating non-existent edition
     */
    public function testUpdateEditionNotFound() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        try {
            \OmegaUp\Controllers\GSoC::apiUpdateEdition(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'edition_id' => 99999,
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('editionNotFound', $e->getMessage());
        }
    }

    /**
     * Test creating idea as admin
     */
    public function testCreateIdeaAsAdmin() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2031, true, null);

        $response = \OmegaUp\Controllers\GSoC::apiCreateIdea(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'edition_id' => $editionId,
            'title' => 'Test Project Idea',
            'brief_description' => 'This is a test idea',
            'skill_level' => 'Medium',
            'status' => 'Proposed',
        ]));

        $this->assertArrayHasKey('idea_id', $response);
        $this->assertGreaterThan(0, $response['idea_id']);

        // Verify in database
        $idea = \OmegaUp\DAO\GSoC::getIdeaById($response['idea_id']);
        $this->assertNotNull($idea);
        $this->assertSame('Test Project Idea', $idea['title']);
        $this->assertSame('Medium', $idea['skill_level']);
        $this->assertSame('Proposed', $idea['status']);
    }

    /**
     * Test creating idea requires admin
     */
    public function testCreateIdeaRequiresAdmin() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);
        $editionId = \OmegaUp\DAO\GSoC::createEdition(2032, true, null);

        try {
            \OmegaUp\Controllers\GSoC::apiCreateIdea(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'edition_id' => $editionId,
                'title' => 'Test Idea',
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Test creating idea with invalid edition
     */
    public function testCreateIdeaInvalidEdition() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        try {
            \OmegaUp\Controllers\GSoC::apiCreateIdea(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'edition_id' => 99999,
                'title' => 'Test Idea',
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('editionNotFound', $e->getMessage());
        }
    }

    /**
     * Test creating idea with empty title
     */
    public function testCreateIdeaEmptyTitle() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2033, true, null);

        try {
            \OmegaUp\Controllers\GSoC::apiCreateIdea(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'edition_id' => $editionId,
                'title' => '   ',
            ]));
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
        }
    }

    /**
     * Test creating idea with invalid skill level
     */
    public function testCreateIdeaInvalidSkillLevel() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2034, true, null);

        try {
            \OmegaUp\Controllers\GSoC::apiCreateIdea(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'edition_id' => $editionId,
                'title' => 'Test Idea',
                'skill_level' => 'InvalidLevel',
            ]));
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Test creating idea with invalid status
     */
    public function testCreateIdeaInvalidStatus() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2035, true, null);

        try {
            \OmegaUp\Controllers\GSoC::apiCreateIdea(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'edition_id' => $editionId,
                'title' => 'Test Idea',
                'status' => 'InvalidStatus',
            ]));
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Test creating idea with invalid blog link
     */
    public function testCreateIdeaInvalidBlogLink() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2036, true, null);

        try {
            \OmegaUp\Controllers\GSoC::apiCreateIdea(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'edition_id' => $editionId,
                'title' => 'Test Idea',
                'blog_link' => 'not-a-valid-url',
            ]));
            $this->fail('Should have thrown InvalidParameterException');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Test updating idea as admin
     */
    public function testUpdateIdeaAsAdmin() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2037, true, null);
        $ideaId = \OmegaUp\DAO\GSoC::createIdea(
            $editionId,
            'Original Title',
            null,
            null,
            null,
            null,
            null,
            null,
            'Proposed',
            null,
            null
        );

        $response = \OmegaUp\Controllers\GSoC::apiUpdateIdea(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'idea_id' => $ideaId,
            'title' => 'Updated Title',
            'status' => 'Accepted',
        ]));

        $this->assertArrayHasKey('updated', $response);
        $this->assertTrue($response['updated']);

        // Verify update in database
        $idea = \OmegaUp\DAO\GSoC::getIdeaById($ideaId);
        $this->assertSame('Updated Title', $idea['title']);
        $this->assertSame('Accepted', $idea['status']);
    }

    /**
     * Test updating idea requires admin
     */
    public function testUpdateIdeaRequiresAdmin() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);
        $editionId = \OmegaUp\DAO\GSoC::createEdition(2038, true, null);
        $ideaId = \OmegaUp\DAO\GSoC::createIdea(
            $editionId,
            'Test Idea',
            null,
            null,
            null,
            null,
            null,
            null,
            'Proposed',
            null,
            null
        );

        try {
            \OmegaUp\Controllers\GSoC::apiUpdateIdea(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'idea_id' => $ideaId,
                'title' => 'Updated Title',
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Test updating non-existent idea
     */
    public function testUpdateIdeaNotFound() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        try {
            \OmegaUp\Controllers\GSoC::apiUpdateIdea(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'idea_id' => 99999,
                'title' => 'Updated Title',
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('ideaNotFound', $e->getMessage());
        }
    }

    /**
     * Test deleting idea as admin
     */
    public function testDeleteIdeaAsAdmin() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        $editionId = \OmegaUp\DAO\GSoC::createEdition(2039, true, null);
        $ideaId = \OmegaUp\DAO\GSoC::createIdea(
            $editionId,
            'Idea to Delete',
            null,
            null,
            null,
            null,
            null,
            null,
            'Proposed',
            null,
            null
        );

        $response = \OmegaUp\Controllers\GSoC::apiDeleteIdea(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'idea_id' => $ideaId,
        ]));

        $this->assertArrayHasKey('deleted', $response);
        $this->assertTrue($response['deleted']);

        // Verify deletion in database
        $idea = \OmegaUp\DAO\GSoC::getIdeaById($ideaId);
        $this->assertNull($idea);
    }

    /**
     * Test deleting idea requires admin
     */
    public function testDeleteIdeaRequiresAdmin() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);
        $editionId = \OmegaUp\DAO\GSoC::createEdition(2040, true, null);
        $ideaId = \OmegaUp\DAO\GSoC::createIdea(
            $editionId,
            'Test Idea',
            null,
            null,
            null,
            null,
            null,
            null,
            'Proposed',
            null,
            null
        );

        try {
            \OmegaUp\Controllers\GSoC::apiDeleteIdea(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'idea_id' => $ideaId,
            ]));
            $this->fail('Should have thrown ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Test deleting non-existent idea
     */
    public function testDeleteIdeaNotFound() {
        ['user' => $admin, 'identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($adminIdentity);

        try {
            \OmegaUp\Controllers\GSoC::apiDeleteIdea(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'idea_id' => 99999,
            ]));
            $this->fail('Should have thrown NotFoundException');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('ideaNotFound', $e->getMessage());
        }
    }

    /**
     * Test getIdeasForTypeScript entry point
     */
    public function testGetIdeasForTypeScript() {
        $response = \OmegaUp\Controllers\GSoC::getIdeasForTypeScript(
            new \OmegaUp\Request()
        );

        $this->assertArrayHasKey('entrypoint', $response);
        $this->assertSame('gsoc_ideas', $response['entrypoint']);
        $this->assertArrayHasKey('templateProperties', $response);
        $this->assertArrayHasKey('title', $response['templateProperties']);
        $this->assertInstanceOf(
            \OmegaUp\TranslationString::class,
            $response['templateProperties']['title']
        );
    }
}
