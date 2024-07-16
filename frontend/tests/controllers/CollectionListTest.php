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
     * Creates 8 quality problems with author
     */
    private function createQualityProblemsWithAuthor() {
        $identities = [];
        $problems = [];

        // Create 4 user with 2 problems each
        for ($i = 0; $i < 4; $i++) {
            ['identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams(
                    ['username' => 'author_' . $i]
                )
            );

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
                    difficulty: 1,
                    quality: 4,
                    tags: [],
                    beforeAC: false
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
                    difficulty: 1,
                    quality: 3,
                    tags: [],
                    beforeAC: false
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
    }

    /**
     * Test for the most frequent tags of a level with a level parameter in a request
     */
    public function testCollectionTags() {
        $problemsTagsMapping = [
            [
                'title' => 'problem_1',
                'alias' => 'problem_1',
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => [
                    'problemTagMatrices',
                    'problemTagInputAndOutput',
                    'problemTagDiophantineEquations',
                ],
            ],
            [
                'title' => 'problem_2',
                'alias' => 'problem_2',
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => [
                    'problemTagMatrices',
                    'problemTagInputAndOutput',
                    'problemTagLoops',
                ],
            ],
            [
                'title' => 'problem_3',
                'alias' => 'problem_3',
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => [
                    'problemTagMatrices',
                    'problemTagArithmetic',
                    'problemTagFunctions',
                ],
            ],
        ];

        // Reviewer user
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );

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
            $r['problem_alias'] = $problemTags['alias'];
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

            // Review problem as quality problem
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'problem_alias' => $problemTags['alias'],
                'nomination' => 'quality_tag',
                'contents' => json_encode([
                    'quality_seal' => true,
                    'tag' => $problemTags['tags'][0],
                ]),
            ]));
        }

        \OmegaUp\Test\Utils::runAggregateFeedback();

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Call getCollectionsDetailsByLevelForTypeScript with a level tag collection type
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
            ])
        )['templateProperties']['payload']['frequentTags'];

        $this->assertSame('problemTagMatrices', $result[0]['name']);
        $this->assertCount(6, $result);
    }

    /**
     * Test for the authors rank with quality problems by a request and the paginator of problems
     */
    public function testCollectionAuthors() {
        self::createQualityProblemsWithAuthor();

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Call getCollectionsDetailsByAuthorForTypeScript with an author collection type
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload']['authorsRanking']['ranking'];

        foreach ($result as $key) {
            $this->assertArrayHasKey('author_ranking', $key);
            $this->assertArrayHasKey('username', $key);
            $this->assertArrayHasKey('name', $key);
            $this->assertArrayHasKey('classname', $key);
        }

        $this->assertCount(4, $result);
    }

    /**
     * Test for the difficulty of quality problems
     */
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
                    difficulty: $i,
                    quality: 3,
                    tags: [],
                    beforeAC: false
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

        // Call getCollectionsDetailsByLevelForTypeScript with easy difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'difficulty' => 'easy'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(2, $result);

        // Call getCollectionsDetailsByLevelForTypeScript with medium difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'difficulty' => 'medium'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(1, $result);

        // Call getCollectionsDetailsByLevelForTypeScript with hard difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'difficulty' => 'hard'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(1, $result);

        // Call getCollectionsDetailsByLevelForTypeScript with all difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'difficulty' => 'all'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(4, $result);

        // Call getCollectionsDetailsByLevelForTypeScript without difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(4, $result);

        // Call getCollectionsDetailsByAuthorForTypeScript with easy difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'difficulty' => 'easy'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(2, $result);

        // Call getCollectionsDetailsByAuthorForTypeScript with medium difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'difficulty' => 'medium'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(1, $result);

        // Call getCollectionsDetailsByAuthorForTypeScript with hard difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'difficulty' => 'hard'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(1, $result);

        // Call getCollectionsDetailsByAuthorForTypeScript with all difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'difficulty' => 'all'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(4, $result);

        // Call getCollectionsDetailsByAuthorForTypeScript without difficulty parameter
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(4, $result);
    }

    /**
     * Test for paginator of problems by level
     */
    public function testProblemsByLevelPaginator() {
        // Reviewer user
        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );

        for ($i = 0; $i < 4; $i++) {
            $problemData = \OmegaUp\Test\Factories\Problem::getRequest();

            // Login user
            $login = self::login($problemData['author']);

            // Call the API
            \OmegaUp\Controllers\Problem::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'title' => 'problem_' . $i,
                'problem_alias' => 'problem_' . $i,
                'problem_level' => 'problemLevelBasicIntroductionToProgramming',
                'author' => 'author_' . $i,
                'source' => 'yo',
                'visibility' => '2',
                'selected_tags' => json_encode([
                    ['tagname' => 'problemTagMatrices', 'public' => true],
                    ['tagname' => 'problemTagInputAndOutput', 'public' => true],
                    ['tagname' => 'problemTagDiophantineEquations', 'public' => true],
                ]),
            ]));

            // Review problem as quality problem
            \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
                'auth_token' => $reviewerLogin->auth_token,
                'problem_alias' => 'problem_' . $i,
                'nomination' => 'quality_tag',
                'contents' => json_encode([
                    'quality_seal' => true,
                    'tag' => 'problemLevelBasicIntroductionToProgramming',
                ]),
            ]));
        }

        \OmegaUp\Test\Utils::runAggregateFeedback();

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Call getCollectionsDetailsByLevelForTypeScript
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'sort_order' => 'asc'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(4, $result);
        $this->assertSame('problem_0', $result[0]['alias']);
        $this->assertSame('problem_1', $result[1]['alias']);
        $this->assertSame('problem_2', $result[2]['alias']);
        $this->assertSame('problem_3', $result[3]['alias']);

        // Call getCollectionsDetailsByLevelForTypeScript with 2 as rowcount and 1 as page
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'rowcount' => '2',
                'page' => '1',
                'sort_order' => 'asc'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(2, $result);
        $this->assertSame('problem_0', $result[0]['alias']);
        $this->assertSame('problem_1', $result[1]['alias']);

        // Call getCollectionsDetailsByLevelForTypeScript with 2 as rowcount and 2 as page
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByLevelForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'rowcount' => '2',
                'page' => '2',
                'sort_order' => 'asc'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(2, $result);
        $this->assertSame('problem_2', $result[0]['alias']);
        $this->assertSame('problem_3', $result[1]['alias']);
    }

    /**
     * Test for paginator of problems by author
     */
    public function testProblemsByAuthorPaginator() {
        self::createQualityProblemsWithAuthor();

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Call getCollectionsDetailsByAuthorForTypeScript
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(8, $result);

        // Call getCollectionsDetailsByAuthorForTypeScript with a username of an author
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'author' => 'author_0'
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(2, $result);

        // Call getCollectionsDetailsByAuthorForTypeScript with a username of an author, 1 as rowcount
        // and 2 as page
        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsByAuthorForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'author' => 'author_0',
                'rowcount' => 1,
                'page' => 2
            ])
        )['templateProperties']['payload']['problems'];

        $this->assertCount(1, $result);
    }
}
