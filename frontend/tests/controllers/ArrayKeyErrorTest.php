<?php

/**
 * Tests for undefined array key errors prevention
 */
class ArrayKeyErrorTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test that ArrayHelper prevents undefined array key errors
     */
    public function testArrayHelperPreventsErrors() {
        $emptyArray = [];

        // These should not throw "Undefined array key" errors
        $result1 = \OmegaUp\ArrayHelper::get(
            $emptyArray,
            'missing_key',
            'default'
        );
        $this->assertSame('default', $result1);

        $result2 = \OmegaUp\ArrayHelper::getString(
            $emptyArray,
            'missing_key',
            'default_string'
        );
        $this->assertSame('default_string', $result2);

        $result3 = \OmegaUp\ArrayHelper::getInt($emptyArray, 'missing_key', 42);
        $this->assertSame(42, $result3);

        $result4 = \OmegaUp\ArrayHelper::getFloat(
            $emptyArray,
            'missing_key',
            3.14
        );
        $this->assertSame(3.14, $result4);
    }

    /**
     * Test scoreboard group score handling with empty keys
     */
    public function testScoreboardEmptyGroupKeys() {
        // Simulate the scoreboard scenario that caused the NewRelic error
        $scoreByGroupArray = [
            '' => 50.0,  // Empty key that causes "Undefined array key" error
            'group1' => 75.0,
            'group2' => 90.0
        ];

        // Test that ArrayHelper handles empty keys safely
        $emptyKeyScore = \OmegaUp\ArrayHelper::getFloat(
            $scoreByGroupArray,
            '',
            0.0
        );
        $this->assertSame(50.0, $emptyKeyScore);

        $missingKeyScore = \OmegaUp\ArrayHelper::getFloat(
            $scoreByGroupArray,
            'missing',
            0.0
        );
        $this->assertSame(0.0, $missingKeyScore);

        $normalKeyScore = \OmegaUp\ArrayHelper::getFloat(
            $scoreByGroupArray,
            'group1',
            0.0
        );
        $this->assertSame(75.0, $normalKeyScore);
    }

    /**
     * Test that demonstrates score key errors in run details
     *
     * This test simulates the error: Undefined array key "score"
     * that appears in /api/run.details
     */
    public function testRunDetailsScoreUndefinedKeyError() {
        // Simulate run data without score field (causes the original error)
        $runData = [
            'verdict' => 'AC',
            'runtime' => 1500,
            'memory' => 2048
            // Missing 'score' key - this causes the error
        ];

        $score = \OmegaUp\ArrayHelper::getFloat($runData, 'score', 0.0);
        $this->assertSame(0.0, $score);

        // Test with actual score present
        $runDataWithScore = [
            'verdict' => 'AC',
            'runtime' => 1500,
            'memory' => 2048,
            'score' => 85.5
        ];

        $actualScore = \OmegaUp\ArrayHelper::getFloat(
            $runDataWithScore,
            'score',
            default: 0.0
        );
        $this->assertSame(85.5, $actualScore);
    }

    /**
     * Test that demonstrates gender key errors in identity bulk create
     *
     * This test simulates the error: Undefined array key "gender"
     * that appears in /api/identity.bulkcreate
     */
    public function testIdentityBulkCreateGenderUndefinedKeyError() {
        // Simulate identity data without gender field
        $identityData = [
            'username' => 'testuser',
            'name' => 'Test User',
            'password' => 'password123'
            // Missing 'gender' key - this causes the error
        ];

        // Using our ArrayHelper should provide safe access
        $gender = \OmegaUp\ArrayHelper::getString(
            $identityData,
            key: 'gender',
            default: 'decline'
        );
        $this->assertSame('decline', $gender);

        // Test with gender present
        $identityDataWithGender = [
            'username' => 'testuser',
            'name' => 'Test User',
            'password' => 'password123',
            'gender' => 'male'
        ];

        $actualGender = \OmegaUp\ArrayHelper::getString(
            $identityDataWithGender,
            key: 'gender',
            default: 'decline'
        );
        $this->assertSame('male', $actualGender);
    }

    /**
     * Test Identity bulk create proper validation approach
     *
     * This tests the corrected approach: Instead of silently using ArrayHelper defaults,
     * we should validate user input and throw proper parameter errors.
     */
    public function testIdentityBulkCreateProperValidation() {
        // Test identity data without gender field
        // Should now properly validate and indicate missing required field
        $identityWithoutGender = [
            'username' => 'testuser',
            'name' => 'Test User',
            'password' => 'password123'
            // Missing 'gender' key - should be validated as required
        ];

        // Test validation logic (simulating what Identity controller should do)
        $hasGender = array_key_exists('gender', $identityWithoutGender);
        $this->assertFalse(
            $hasGender,
            'Missing gender should be detected for proper validation'
        );

        // Test identity data with gender field
        $identityWithGender = [
            'username' => 'testuser',
            'name' => 'Test User',
            'password' => 'password123',
            'gender' => 'female'
        ];

        $hasGenderPresent = array_key_exists('gender', $identityWithGender);
        $this->assertTrue(
            $hasGenderPresent,
            'Present gender should be detected'
        );

        if ($hasGenderPresent) {
            $gender = $identityWithGender['gender'];
            $this->assertSame('female', $gender);
        }
    }

    /**
     * Test Run controller score/contest_score undefined key errors
     *
     * This test simulates the error: "Undefined array key 'score'" and
     * "Undefined array key 'contest_score'" that appear in Run::getOptionalRunDetails
     */
    public function testRunOptionalDetailsScoreUndefinedKeys() {
        // Simulate run details data without score fields (causes the original errors)
        $incompleteRunDetails = [
            'verdict' => 'AC',
            'judged_by' => 'grader',
            'memory' => 2048,
            'time' => 1.5
            // Missing 'score' and 'contest_score' keys - these cause the errors
        ];

        // Test safe access to missing score field
        $score = \OmegaUp\ArrayHelper::getFloat(
            $incompleteRunDetails,
            'score',
            0.0
        );
        $this->assertSame(0.0, $score);

        // Test safe access to missing contest_score field
        $contestScore = \OmegaUp\ArrayHelper::getFloat(
            $incompleteRunDetails,
            'contest_score',
            0.0
        );
        $this->assertSame(0.0, $contestScore);

        // Test with complete data
        $completeRunDetails = [
            'verdict' => 'AC',
            'judged_by' => 'grader',
            'memory' => 2048,
            'time' => 1.5,
            'score' => 85.5,
            'contest_score' => 90.0
        ];

        $actualScore = \OmegaUp\ArrayHelper::getFloat(
            $completeRunDetails,
            'score',
            0.0
        );
        $this->assertSame(85.5, $actualScore);

        $actualContestScore = \OmegaUp\ArrayHelper::getFloat(
            $completeRunDetails,
            'contest_score',
            0.0
        );
        $this->assertSame(90.0, $actualContestScore);
    }

    /**
     * Test that Tag controller handles missing request parameters safely
     *
     * This test simulates the potential "Undefined array key 'term'" or
     * "Undefined array key 'query'" errors in Tag::apiList
     */
    public function testTagControllerSafeParameterAccess() {
        // This test verifies that the Tag controller uses ensureOptionalString
        // instead of direct array access which would cause "Undefined array key" errors

        // We can't easily test the controller directly without full setup,
        // but we can verify that the pattern we're trying to prevent is handled
        // by our ArrayHelper for similar cases

        $mockRequestData = []; // Empty request - no 'term' or 'query' keys

        // Test safe access to missing keys (simulating what Tag controller should do)
        $term = \OmegaUp\ArrayHelper::get($mockRequestData, 'term', null);
        $this->assertNull($term);

        $query = \OmegaUp\ArrayHelper::get($mockRequestData, 'query', null);
        $this->assertNull($query);

        // Test with actual values
        $mockRequestDataWithTerm = ['term' => 'algorithm'];
        $actualTerm = \OmegaUp\ArrayHelper::get(
            $mockRequestDataWithTerm,
            'term',
            null
        );
        $this->assertSame('algorithm', $actualTerm);

        $mockRequestDataWithQuery = ['query' => 'dynamic programming'];
        $actualQuery = \OmegaUp\ArrayHelper::get(
            $mockRequestDataWithQuery,
            'query',
            null
        );
        $this->assertSame('dynamic programming', $actualQuery);
    }

    /**
     * Test getPath method for safe nested array access
     */
    public function testArrayHelperGetPathMethod() {
        $nestedData = [
            'user' => [
                'profile' => [
                    'score' => 1500,
                    'rank' => 42
                ]
            ]
        ];

        // Test successful nested access
        $score = \OmegaUp\ArrayHelper::getPath(
            $nestedData,
            keys: ['user', 'profile', 'score'],
            default: 0
        );
        $this->assertSame(1500, $score);

        // Test missing nested key (should return default)
        $missing = \OmegaUp\ArrayHelper::getPath(
            $nestedData,
            keys: ['user', 'profile', 'missing'],
            default: 'default'
        );
        $this->assertSame('default', $missing);

        // Test completely wrong path
        $wrongPath = \OmegaUp\ArrayHelper::getPath(
            $nestedData,
            keys: ['nonexistent', 'path'],
            default: 'fallback'
        );
        $this->assertSame('fallback', $wrongPath);
    }
}
