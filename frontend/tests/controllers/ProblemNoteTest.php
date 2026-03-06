<?php
/**
 * Test for ProblemNoteController
 */
class ProblemNoteTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Tests saving a note
     */
    public function testSaveNote() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\ProblemNote::apiSave(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
                'note_text' => 'This is a test note',
            ])
        );

        $this->assertSame('ok', $response['status']);

        // Verify note was created via apiGet
        $getResponse = \OmegaUp\Controllers\ProblemNote::apiGet(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertSame('This is a test note', $getResponse['note_text']);
    }

    /**
     * Tests updating an existing note
     */
    public function testUpdateNote() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);

        // Create initial note
        \OmegaUp\Controllers\ProblemNote::apiSave(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
                'note_text' => 'Initial note',
            ])
        );

        // Update the note
        \OmegaUp\Controllers\ProblemNote::apiSave(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
                'note_text' => 'Updated note',
            ])
        );

        // Verify the note was updated
        $getResponse = \OmegaUp\Controllers\ProblemNote::apiGet(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertSame('Updated note', $getResponse['note_text']);
    }

    /**
     * Tests deleting a note
     */
    public function testDeleteNote() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);

        // Create a note
        \OmegaUp\Controllers\ProblemNote::apiSave(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
                'note_text' => 'Note to delete',
            ])
        );

        // Delete the note
        $response = \OmegaUp\Controllers\ProblemNote::apiDelete(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertSame('ok', $response['status']);

        // Verify note was deleted
        $getResponse = \OmegaUp\Controllers\ProblemNote::apiGet(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertNull($getResponse['note_text']);
    }

    /**
     * Tests getting a non-existent note
     */
    public function testGetNonExistentNote() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);
        $getResponse = \OmegaUp\Controllers\ProblemNote::apiGet(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertNull($getResponse['note_text']);
    }

    /**
     * Tests listing notes for multiple problems
     */
    public function testListNotes() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData1 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData2 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData3 = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);

        // Save notes on two problems
        \OmegaUp\Controllers\ProblemNote::apiSave(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData1['problem']->alias,
                'note_text' => 'Note for problem 1',
            ])
        );
        \OmegaUp\Controllers\ProblemNote::apiSave(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData2['problem']->alias,
                'note_text' => 'Note for problem 2',
            ])
        );

        // List notes
        $response = \OmegaUp\Controllers\ProblemNote::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame(2, $response['total']);

        $aliases = array_map(
            function ($note) {
                return $note['alias'];
            },
            $response['notes']
        );
        $this->assertContains($problemData1['problem']->alias, $aliases);
        $this->assertContains($problemData2['problem']->alias, $aliases);
        $this->assertNotContains($problemData3['problem']->alias, $aliases);
    }

    /**
     * Tests that notes are user-specific
     */
    public function testNoteIsUserSpecific() {
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // User1 saves a note
        $login1 = self::login($identity1);
        \OmegaUp\Controllers\ProblemNote::apiSave(
            new \OmegaUp\Request([
                'auth_token' => $login1->auth_token,
                'problem_alias' => $problemData['problem']->alias,
                'note_text' => 'User 1 private note',
            ])
        );

        // User2 should not see the note
        $login2 = self::login($identity2);
        $getResponse = \OmegaUp\Controllers\ProblemNote::apiGet(
            new \OmegaUp\Request([
                'auth_token' => $login2->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $this->assertNull($getResponse['note_text']);

        // User2's list should be empty
        $listResponse = \OmegaUp\Controllers\ProblemNote::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login2->auth_token,
            ])
        );

        $this->assertSame(0, $listResponse['total']);
    }

    /**
     * Tests saving a note without login
     */
    public function testSaveNoteWithoutLogin() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        try {
            \OmegaUp\Controllers\ProblemNote::apiSave(
                new \OmegaUp\Request([
                    'problem_alias' => $problemData['problem']->alias,
                    'note_text' => 'Unauthorized note',
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            $this->assertSame('loginRequired', $e->getMessage());
        }
    }

    /**
     * Tests saving a note on an invalid problem
     */
    public function testSaveNoteInvalidProblem() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\ProblemNote::apiSave(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'problem_alias' => 'nonexistent-problem-alias',
                    'note_text' => 'Note for nonexistent problem',
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('problemNotFound', $e->getMessage());
        }
    }

    /**
     * Tests saving a note that exceeds character limit
     */
    public function testSaveNoteTooLong() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\ProblemNote::apiSave(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'problem_alias' => $problemData['problem']->alias,
                    'note_text' => str_repeat('a', 2001),
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Expected: note text exceeds 2000 character limit
            $this->assertNotNull($e->getMessage());
        }
    }

    /**
     * Tests deleting a non-existent note
     */
    public function testDeleteNonExistentNote() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\ProblemNote::apiDelete(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'problem_alias' => $problemData['problem']->alias,
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('recordNotFound', $e->getMessage());
        }
    }
}
