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
        )['smartyProperties']['payload']['frequentTags'];

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
                    /*$difficulty=*/1,
                    /*$quality=*/4,
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
                    /*$difficulty=*/1,
                    /*$quality=*/3,
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
        )['smartyProperties']['payload']['authorsRanking']['ranking'];

        foreach ($result as $key) {
            $this->assertArrayHasKey('author_ranking', $key);
            $this->assertArrayHasKey('username', $key);
            $this->assertArrayHasKey('name', $key);
            $this->assertArrayHasKey('classname', $key);
        }

        $this->assertCount(4, $result);
    }

    public function testDifficultyOfQualityProblems() {
        $identities = [];
        $problems = [];

        // Create 4 user with a problem each
        for ($i = 0; $i < 4; $i++) {
            ['identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();

            $problems[] = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
                $identities[$i]
            );
        }

        // Create 6 non-author users
        for ($i = 4; $i < 10; $i++) {
            ['identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();
        }

        // The 6 users solve and rate the 4 problems
        for ($i = 0; $i < 4; $i++) {
            for ($j = 4; $j < 10; $j++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problems[$i],
                    $identities[$j]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.5, 'AC');
                \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
                    $identities[$j],
                    $problems[$i]['request']['problem_alias'],
                    /*$difficulty=*/$i,
                    /*$quality=*/3,
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

        // Call getCollectionsDetailsByLevelForSmarty with easy difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'difficulty' => 'easy'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(2, $result);

        // Call getCollectionsDetailsByLevelForSmarty with medium difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'difficulty' => 'medium'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(1, $result);

        // Call getCollectionsDetailsByLevelForSmarty with hard difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'difficulty' => 'hard'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(1, $result);

        // Call getCollectionsDetailsByLevelForSmarty with all difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'difficulty' => 'all'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(4, $result);

        // Call getCollectionsDetailsByLevelForSmarty without difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(4, $result);

        // Call getCollectionsDetailsByAuthorForSmarty with easy difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'difficulty' => 'easy'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(2, $result);

        // Call getCollectionsDetailsByAuthorForSmarty with medium difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'difficulty' => 'medium'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(1, $result);

        // Call getCollectionsDetailsByAuthorForSmarty with hard difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'difficulty' => 'hard'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(1, $result);

        // Call getCollectionsDetailsByAuthorForSmarty with all difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'difficulty' => 'all'
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(4, $result);

        // Call getCollectionsDetailsByAuthorForSmarty without difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertCount(4, $result);
    }
}
