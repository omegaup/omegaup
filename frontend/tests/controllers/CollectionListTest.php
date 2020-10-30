<?php

class CollectionListTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        // Get File Uploader Mock and tell Omegaup API to use it
        \OmegaUp\FileHandler::setFileUploaderForTesting(
            $this->createFileUploaderMock()
        );

        // Reviewers
        \OmegaUp\Test\Factories\QualityNomination::initQualityReviewers();
        \OmegaUp\Test\Factories\QualityNomination::initTopicTags();
    }

    /**
     * Test for the most frequent tags of a level with a level parameter in a request
     */
    public function testCollectionTags() {
        $problemsTagsMapping = [
            [
                'title' => 'problem_1',
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => [
                    'problemTagMatrices',
                    'problemTagInputAndOutput',
                    'problemTagDiophantineEquations',
                ],
            ],
            [
                'title' => 'problem_2',
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => [
                    'problemTagMatrices',
                    'problemTagInputAndOutput',
                    'problemTagLoops',
                ],
            ],
            [
                'title' => 'problem_3',
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => [
                    'problemTagMatrices',
                    'problemTagArithmetic',
                    'problemTagFunctions',
                ],
            ],
        ];

        foreach ($problemsTagsMapping as $problemTags) {
            // Get the problem data
            $problemData = \OmegaUp\Test\Factories\Problem::getRequest(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'problem_level' => $problemTags['level'],
                ])
            );

            // Assign data to the request
            $r = $problemData['request'];
            $r['title'] = $problemTags['title'];
            $problemAuthor = $problemData['author'];

            // Login user
            $login = self::login($problemAuthor);
            $r['auth_token'] = $login->auth_token;
            $tags = [];
            foreach ($problemTags['tags'] as $tag) {
                $tags[] = ['tagname' => $tag, 'public' => true];
            }
            $r['selected_tags'] = json_encode($tags);

            // Call the API
            \OmegaUp\Controllers\Problem::apiCreate($r);
        }

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Call getCollectionsDetailsByLevelForSmarty with a level tag collection type
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
            ])
        )['smartyProperties']['payload']['collection'];

        $this->assertEquals('problemTagMatrices', $result['0']['alias']);
        $this->assertCount(6, $result);
    }

    /**
     * Test for the authors rank with a by a request
     */
    public function testCollectionAuthors() {
        $identities = [];
        $problems = [];

        // Create 4 user with 2 problems each
        for ($i = 0; $i < 4; $i++) {
            ['identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();

            for ($j = 0; $j < 2; $j++) {
                $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
                    $identities[$i]
                );
            }
        }

        // The four users solve and rate the 8 problems
        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 8; $j++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problems[$j],
                    $identities[$i]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.5, 'AC');
                \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                    $identities[$i],
                    $problems[$j]['request']['problem_alias'],
                    1,
                    4,
                    [],
                    false
                );
            }
        }

        // Create another 4 non-author users that solve and rate the problems
        for ($i = 4; $i < 8; $i++) {
            ['identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();

            for ($j = 0; $j < 8; $j++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problems[$j],
                    $identities[$i]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.5, 'AC');
                \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                    $identities[$i],
                    $problems[$j]['request']['problem_alias'],
                    1,
                    3,
                    [],
                    false
                );
            }
        }

        \OmegaUp\Test\Utils::runAggregateFeedback();
        \OmegaUp\Test\Utils::runUpdateRanks();

        // Review problems as quality problems
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        foreach ($problems as $problem) {
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'problem_alias' => $problem['request']['problem_alias'],
                'nomination' => 'quality_tag',
                'contents' => json_encode([
                    'quality_seal' => true,
                    'tag' => 'problemLevelBasicIntroductionToProgramming',
                    'tags' => ['problemTagBitManipulation', 'problemTagRecursion'],
                ]),
            ]));
        }

        \OmegaUp\Test\Utils::runAggregateFeedback();

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Call getCollectionsDetailsByAuthorForSmarty with an author collection type
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload']['authors'];

        foreach ($result as $key) {
            $this->assertArrayHasKey('username', $key);
            $this->assertArrayHasKey('name', $key);
        }

        $this->assertCount(4, $result);
    }
}
