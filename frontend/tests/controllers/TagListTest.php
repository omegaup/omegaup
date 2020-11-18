<?php

class TagListTest extends \OmegaUp\Test\ControllerTestCase {
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
     * Basic test for verify empty parameter in a request
     */
    public function testInvalidParameter() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Tag::apiFrequentTags(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ]));
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterEmpty', $e->getMessage());
        }
    }

    /**
     * Test for verify the most frequent tags of problems given a level
     */
    public function testGetListOfFrequentTags() {
        // Create problems names with levels and tags
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
                    'problemTagArrays',
                ],
            ],
            [
                'title' => 'problem_3',
                'alias' => 'problem_3',
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => [
                    'problemTagMatrices',
                    'problemTagInputAndOutput',
                    'problemTagDiophantineEquations',
                ],
            ],
            [
                'title' => 'problem_4',
                'alias' => 'problem_4',
                'level' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => [
                    'problemTagMatrices',
                    'problemTagDiophantineEquations',
                ],
            ],
            [
                'title' => 'problem_5',
                'alias' => 'problem_5',
                'level' => 'problemLevelIntermediateMathsInProgramming',
                'tags' => [
                    'problemTagModularArithmetic',
                    'problemTagNumberTheory',
                ],
            ],
            [
                'title' => 'problem_6',
                'alias' => 'problem_6',
                'level' => 'problemLevelIntermediateMathsInProgramming',
                'tags' => [
                    'problemTagModularArithmetic',
                    'problemTagNumberTheory',
                ],
            ],
            [
                'title' => 'problem_7',
                'alias' => 'problem_7',
                'level' => 'problemLevelIntermediateMathsInProgramming',
                'tags' => [
                    'problemTagModularArithmetic',
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
                    'tag' => $problemTags['level'],
                ]),
            ]));
        }

        \OmegaUp\Test\Utils::runAggregateFeedback();

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Login
        $login = self::login($identity);
        $request = \OmegaUp\Controllers\Tag::apiFrequentTags(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemLevel' => 'problemLevelBasicIntroductionToProgramming',
                'rows' => 15
            ])
        );

        $exepectedTagsList = [
            ['alias' => 'problemTagMatrices'],
            ['alias' => 'problemTagDiophantineEquations'],
            ['alias' => 'problemTagInputAndOutput'],
            ['alias' => 'problemTagArrays'],
        ];

        $this->assertEquals($request['frequent_tags'], $exepectedTagsList);
    }
}
