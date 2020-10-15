<?php
/**
 * Description of ProblemListTest
 *
 * @author joemmanuel
 */
class ProblemListTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        \OmegaUp\Test\Factories\QualityNomination::initQualityReviewers();
        \OmegaUp\Test\Factories\QualityNomination::initTopicTags();
    }

    /**
     * Gets the list of problems
     */
    public function testProblemList() {
        // Get 3 problems
        $n = 3;
        foreach (range(0, $n - 1) as $i) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'visibility' => 'promoted',
                ])
            );
        }

        // Get 1 problem private, should not appear
        $privateProblemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'private',
            ])
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        // Check that all public problems are there
        foreach (range(0, $n - 1) as $i) {
            $count = 0;
            $problemAlias = $problemData[$i]['request']['problem_alias'];
            foreach ($response['results'] as $problemResponse) {
                $this->assertArrayHasKey(
                    'problem_id',
                    $problemResponse,
                    'Array should contain problem_id'
                );
                if ($problemResponse === 'ok') {
                    continue;
                }

                if ($problemResponse['alias'] === $problemAlias) {
                    $count++;
                }
            }
            if ($count != 1) {
                $this->fail("Problem {$problemAlias} is not exactly once.");
            }
        }

        // Check private problem is not there
        $exists = false;
        $problemAlias = $privateProblemData['request']['problem_alias'];
        foreach ($response['results'] as $problemResponse) {
            if ($problemResponse['alias'] === $problemAlias) {
                $exists = true;
                break;
            }
            // Check if quality_histogram and difficulty_histogram fields are
            // being returned also
            $this->assertTrue(
                array_key_exists(
                    'quality_histogram',
                    $problemResponse
                )
            );
            $this->assertTrue(
                array_key_exists(
                    'difficulty_histogram',
                    $problemResponse
                )
            );
        }

        if ($exists) {
            $this->fail("Private problem {$problemAlias} is in the list.");
        }
    }

    /**
     * Test getting a list of problems while filtering by tag.
     */
    public function testProblemListWithTags() {
        // Get 3 problems
        $n = 3;
        $tags = [
            'problemTagArrays',
            'problemTagBigData',
            'problemTagGreedyAlgorithms',
            'problemTagHashing',
            'problemTagMatrices',
            'problemTagMaxFlow',
            'problemTagMeetInTheMiddle',
            'problemTagNumberTheory',
        ];
        for ($i = 0; $i < $n; $i++) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'promoted'
            ]));
            for ($j = 0; $j <= $i; $j++) {
                \OmegaUp\Test\Factories\Problem::addTag(
                    $problemData[$i],
                    $tags[$j],
                    1 /* public */
                );
            }
        }

        // Get 1 problem private, should not appear
        $privateProblemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private'
        ]));
        for ($j = 0; $j < $n; $j++) {
            \OmegaUp\Test\Factories\Problem::addTag(
                $privateProblemData,
                $tags[$j],
                1 /* public */
            );
        }

        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Test one tag at a time
        for ($j = 0; $j < $n; $j++) {
            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => $tags[$j],
            ]));
            // $n public problems but not the private problem that has all tags.
            // But only problems $j or later have tag $j.
            $this->assertCount($n - $j, $response['results']);
        }

        // Test multiple tags at a time
        $expectedTags = [];
        for ($j = 0; $j < $n; $j++) {
            $expectedTags[] = $tags[$j];
            $plainTags = implode(',', $expectedTags);

            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => $plainTags,
            ]));
            // $n public problems but not the private problem that has all tags.
            // But only problems $j or later have tags[0] through tags[$j].
            $this->assertCount($n - $j, $response['results']);
        }
    }

    /**
     * Test getting a list of problems using Problem Finder's filters:
     * - tags
     * - karel vs language
     * - difficulty range
     * - Sort by: quality vs points vs submissions
     */
    public function testProblemFinderSearch() {
        // Create 5 problems
        // - Even problems could be solved using Karel, odd ones don't
        // - Each one will have i tags, where i equals the number of the problem
        $n = 5;
        $karel_problem = 'kj,kp,cpp11-gcc,c11-gcc'; // Karel problems should allow kj AND kp extensions
        $tags = [
            'problemTagArrays',
            'problemTagBigData',
            'problemTagGreedyAlgorithms',
            'problemTagHashing',
            'problemTagMatrices',
            'problemTagMaxFlow',
            'problemTagMeetInTheMiddle',
            'problemTagNumberTheory',
        ];
        for ($i = 0; $i < $n; $i++) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'promoted',
                'languages' => $i % 2 == 0 ? $karel_problem : 'kj,cpp11-gcc,c11-gcc',
            ]));
            for ($j = 0; $j <= $i; $j++) {
                \OmegaUp\Test\Factories\Problem::addTag(
                    $problemData[$i],
                    $tags[$j],
                    1 /* public */
                );
            }
        }

        // 5 users are going to be created, each i user will solve i problems
        // and all users are going to send their feedback to the first problem
        $users = [];
        $identities = [];
        for ($i = 0; $i < 5; $i++) {
            ['user' => $users[$i], 'identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();
            for ($j = 0; $j <= $i; $j++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData[$j],
                    $identities[$i]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($runData);
            }
            $login[] = self::login($identities[$i]);
        }

        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[0],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            3, // quality
            [],
            false
        );

        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[1],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            4, // quality
            [],
            false
        );

        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[2],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            2, // quality
            [],
            false
        );

        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[3],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            4, // quality
            [],
            false
        );

        \OmegaUp\Test\Factories\QualityNomination::createSuggestion(
            $identities[4],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            4, // quality
            [],
            false
        );

        \OmegaUp\Test\Utils::runAggregateFeedback();

        // Filter 0:
        // - Containing test-tag-0
        // - Only Karel problems
        // - No Difficulty Range - It must show all problems with the selected
        //                         tag and languages
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login[0]->auth_token,
            'tag' => $tags[0],
            'programming_languages' => 'kp,kj',
        ]));
        $this->assertCount(3, $response['results']);

        // Filter 1:
        // - Containing tag-0
        // - Only Karel problems
        // - Difficulty between 0 and 3
        // - Sorted by popularity
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login[0]->auth_token,
            'tag' => $tags[0],
            'programming_languages' => 'kp,kj',
            'difficulty_range' => '0,3',
            'order_by' => 'submissions',
        ]));
        $this->assertCount(2, $response['results']);
        $this->assertEquals(
            $problemData[2]['request']['problem_alias'],
            $response['results'][0]['alias']
        );
        $this->assertEquals(
            $problemData[4]['request']['problem_alias'],
            $response['results'][1]['alias']
        );

        // Filter 2:
        // - Containing tag-0 or/and tag-3
        // - Difficulty between 0 and 4
        // - Sorted by quality
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login[0]->auth_token,
            'tag' => "$tags[0],$tags[3]",
            'require_all_tags' => false,
            'difficulty_range' => '0,4',
            'order_by' => 'quality',
        ]));
        $this->assertCount(5, $response['results']);
        $this->assertEquals(
            $problemData[0]['request']['problem_alias'],
            $response['results'][0]['alias']
        );

        // Filter 3:
        // - Containing tag-2 or/and tag-3
        // - Only karel
        // - Difficulty between 2 and 4
        // - Sorted by quality
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login[0]->auth_token,
            'tag' => [$tags[2], $tags[3]],
            'require_all_tags' => false,
            'difficulty_range' => '1,4',
            'order_by' => 'quality',
        ]));
        $this->assertEmpty($response['results']);
    }

    /**
     * Tests problem lists when searching by tag when tags are not public.
     */
    public function testProblemListWithPrivateTags() {
        [
            'user' => $admin,
            'identity' => $identityAdmin
        ] = \OmegaUp\Test\Factories\User::createAdminUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'admin']
            )
        );
        ['user' => $userA, 'identity' => $identityA] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'user_a']
            )
        );
        ['user' => $userB, 'identity' => $identityB] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'user_b']
            )
        );
        ['user' => $otherUser, 'identity' => $otherIdentity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => 'other']
            )
        );

        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'promoted',
            'author' => $identityAdmin
        ]));
        $private_problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
            'author' => $identityAdmin
        ]));
        $problem_a = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'promoted',
            'author' => $identityA
        ]));
        $private_problem_a = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
            'author' => $identityA
        ]));
        $problem_b = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'promoted',
            'author' => $identityB
        ]));
        $private_problem_b = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
            'author' => $identityB
        ]));

        $all_problems = [$problem, $private_problem,
                         $problem_a, $private_problem_a, $problem_b, $private_problem_b];
        // Tag each problem with 3 private tags.
        foreach ($all_problems as $problem) {
            \OmegaUp\Test\Factories\Problem::addTag(
                $problem,
                'problemTagArrays',
                1 /* public */
            );
            \OmegaUp\Test\Factories\Problem::addTag(
                $problem,
                'problemTagBigData',
                1 /* public */
            );
            \OmegaUp\Test\Factories\Problem::addTag(
                $problem,
                'c',
                0 /* public */
            );
        }

        $all_users = [$identityAdmin, $identityA, $identityB, $otherIdentity];
        $tag_a_results = [
            6,      // admin user can see all 6 problems
            4,      // User A sees 3 public problems and private_problem_a
            4,      // User B sees 3 public problems and private_problem_b
            3,      // Random user sees only 3 public problems
        ];
        // Same thing when searching for tags "problemTagArrays" and "problemTagBigData", since tags problemTagArrays and problemTagBigData are public
        $tag_ab_results = $tag_a_results;
        // But searching for tags "problemTagArrays" and "c" won't give other users' problems
        // because tag "c" is private.
        $tag_ac_results = [
            6,      // admin can still see all problems
            2,      // User a can only see their 2 problems
            2,      // User b can only see their own 2 problems
            0,      // Random user can't see any problem at all
        ];
        for ($i = 0; $i < 4; $i++) {
            $login = self::login($all_users[$i]);

            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => 'problemTagArrays',
            ]));
            $this->assertCount($tag_a_results[$i], $response['results']);

            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => 'problemTagArrays,problemTagBigData',
            ]));
            $this->assertCount($tag_ab_results[$i], $response['results']);

            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => 'problemTagArrays,c',
            ]));
            $this->assertCount($tag_ac_results[$i], $response['results']);
        }
    }
    /**
     * Limit the output to one problem we know
     */
    public function testLimitOffset() {
        // Get 3 problems
        $n = 3;
        for ($i = 0; $i < $n; $i++) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'promoted'
            ]));
        }

        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'rowcount' => 1,
            'offset' => 1,
        ]));
        $this->assertCount(1, $response['results']);
        $this->assertEquals(
            $problemData[1]['request']['problem_alias'],
            $response['results'][0]['alias']
        );
    }

    /**
     * The author should see his problems as well
     *
     */
    public function testPrivateProblemsShowToAuthor() {
        ['user' => $author, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $anotherAuthor, 'identity' => $anotherIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $problemDataPublic = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'promoted',
            'author' => $identity
        ]));
        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
            'author' => $identity
        ]));
        $anotherProblemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
            'author' => $anotherIdentity
        ]));

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertArrayContainsInKey(
            $response['results'],
            'alias',
            $problemDataPrivate['request']['problem_alias']
        );
    }

    /**
     * The author should see his problems as well
     *
     */
    public function testAllPrivateProblemsShowToAdmin() {
        ['user' => $author, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $problemDataPublic = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'promoted',
            'author' => $identity
        ]));
        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
            'author' => $identity
        ]));

        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $login = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertArrayContainsInKey(
            $response['results'],
            'alias',
            $problemDataPrivate['request']['problem_alias']
        );
    }

    /**
     * An added admin should see those problems as well
     */
    public function testAllPrivateProblemsShowToAddedAdmin() {
        ['user' => $author, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
            'author' => $identity
        ]));

        ['user' => $addedAdmin, 'identity' => $addedIdentityAdmin] = \OmegaUp\Test\Factories\User::createUser();

        $adminLogin = self::login($addedIdentityAdmin);
        $r = new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]);

        // Should not be contained in problem list.
        $response = \OmegaUp\Controllers\Problem::apiList($r);
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'alias',
            $problemDataPrivate['request']['problem_alias']
        );

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemDataPrivate['request']['problem_alias'],
            'usernameOrEmail' => $addedIdentityAdmin->username,
        ]));

        // Now it should be visible.
        $response = \OmegaUp\Controllers\Problem::apiList($r);
        $this->assertArrayContainsInKey(
            $response['results'],
            'alias',
            $problemDataPrivate['request']['problem_alias']
        );
    }

    /**
     * An added admin group should see those problems as well
     */
    public function testAllPrivateProblemsShowToAddedAdminGroup() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'private',
                'author' => $identity,
            ])
        );
        $alias = $problemDataPrivate['request']['problem_alias'];

        [
            'identity' => $addedIdentityAdmin,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($addedIdentityAdmin);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        // Should not be contained in problem list.
        $response = \OmegaUp\Controllers\Problem::apiList($r);
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'alias',
            $alias
        );

        $response = \OmegaUp\Controllers\Problem::apiAdminList($r);
        $this->assertArrayNotContainsInKey(
            $response['problems'],
            'alias',
            $alias
        );
        foreach ($response['problems'] as $problem) {
            $this->assertArrayHasKey(
                'problem_id',
                $problem,
                'Array should contain problem_id'
            );
        }

        $authorLogin = self::login($identity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $identity,
            null,
            null,
            null,
            $authorLogin
        );
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $group,
            $addedIdentityAdmin,
            $authorLogin
        );
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $group,
            $identity,
            $authorLogin
        );

        $response = \OmegaUp\Controllers\Problem::apiAddGroupAdmin(
            new \OmegaUp\Request([
                'auth_token' => $authorLogin->auth_token,
                'problem_alias' => $problemDataPrivate['request']['problem_alias'],
                'group' => $group['group']->alias,
            ])
        );

        // Now it should be visible.
        $response = \OmegaUp\Controllers\Problem::apiList($r);
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'alias',
            $alias
        );

        $response = \OmegaUp\Controllers\Problem::apiAdminList($r);
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['problems'],
            'alias',
            $alias
        );
    }

    /**
     * Authors with admin groups should only see each problem once.
     */
    public function testAuthorOnlySeesProblemsOnce() {
        ['user' => $author, 'identity' => $authorIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'private',
            'author' => $authorIdentity
        ]));
        $alias = $problemDataPrivate['request']['problem_alias'];

        $authorLogin = self::login($authorIdentity);
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $authorIdentity,
            null,
            null,
            null,
            $authorLogin
        );
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $group,
            $identity,
            $authorLogin
        );
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $group,
            $authorIdentity,
            $authorLogin
        );

        $response = \OmegaUp\Controllers\Problem::apiAddGroupAdmin(new \OmegaUp\Request([
            'auth_token' => $authorLogin->auth_token,
            'problem_alias' => $problemDataPrivate['request']['problem_alias'],
            'group' => $group['group']->alias,
        ]));

        // It should be visible just once.
        $r = new \OmegaUp\Request([
            'auth_token' => $authorLogin->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Problem::apiList($r);
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'alias',
            $alias
        );

        $response = \OmegaUp\Controllers\Problem::apiAdminList($r);
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['problems'],
            'alias',
            $alias
        );
    }

    /**
     * An author that belongs to an admin group should not see repeated problems.
     */
    public function testPublicProblemsPlusAddedAdminGroup() {
        ['user' => $author, 'identity' => $authorIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $helper, 'identity' => $helperIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'promoted',
            'author' => $authorIdentity
        ]));

        $group = \OmegaUp\Test\Factories\Groups::createGroup($authorIdentity);
        \OmegaUp\Test\Factories\Groups::addUserToGroup($group, $helperIdentity);

        $login = self::login($authorIdentity);
        $response = \OmegaUp\Controllers\Problem::apiAddGroupAdmin(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemDataPrivate['request']['problem_alias'],
            'group' => $group['group']->alias,
        ]));

        // This should be visible exactly once.
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertArrayContainsInKeyExactlyOnce(
            $response['results'],
            'alias',
            $problemDataPrivate['request']['problem_alias']
        );
    }

    /**
     * Test myList API
     */
    public function testMyList() {
        // Get 3 problems
        ['user' => $author, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $n = 3;
        for ($i = 0; $i < $n; $i++) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'promoted',
                'author' => $identity
            ]));
        }

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiMyList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));
        $this->assertEquals(3, count($response['problems']));
        $this->assertEquals(
            $problemData[2]['request']['problem_alias'],
            $response['problems'][0]['alias']
        );
    }

    /**
     * Logged-in users will have their best scores for all problems
     */
    public function testListContainsScores() {
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemDataNoRun = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemDataDecimal = \OmegaUp\Test\Factories\Problem::createProblem();

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runDataDecimal = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataDecimal,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataDecimal, '.123456', 'PA');

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        // Validate results
        foreach ($response['results'] as $responseProblem) {
            if ($responseProblem['alias'] === $problemData['request']['problem_alias']) {
                if ($responseProblem['score'] != 100.00) {
                    $this->fail('Expected to see 100 score for this problem');
                }
            } elseif ($responseProblem['alias'] === $problemDataDecimal['request']['problem_alias']) {
                if ($responseProblem['score'] != 12.35) {
                    $this->fail('Expected to see 12.34 score for this problem');
                }
            } else {
                if ($responseProblem['score'] != 0) {
                    $this->fail('Expected to see 0 score for this problem');
                }
            }
        }
    }

    /**
     * Test that non-logged in users dont have score set
     */
    public function testListScoresForNonLoggedIn() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $response = \OmegaUp\Controllers\Problem::apiList(
            new \OmegaUp\Request()
        );

        // Validate results
        foreach ($response['results'] as $responseProblem) {
            $this->assertEquals(
                '0',
                $responseProblem['score'],
                'Expecting score to be not set for non-logged in users'
            );
        }
    }

    /**
     * Test List API with query param
     */
    public function testListWithAliasQuery() {
        $problemDataPublic = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'promoted',
            ])
        );
        $problemDataPrivate = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'private',
            ])
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);

        // Expect public problem only
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'query' => substr($problemDataPublic['request']['title'], 2, 8),
        ]));
        $this->assertArrayContainsInKey(
            $response['results'],
            'alias',
            $problemDataPublic['request']['problem_alias']
        );

        // Expect 0 problems, matches are private for $user
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'query' => substr($problemDataPrivate['request']['title'], 2, 8),
        ]));
        $this->assertArrayNotContainsInKey(
            $response['results'],
            'alias',
            $problemDataPublic['request']['problem_alias']
        );

        // Expect 1 problem, admin can see private problem
        {
            ['identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();
            $adminLogin = self::login($identityAdmin);
            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'query' => substr(
                    $problemDataPrivate['request']['title'],
                    2,
                    5
                ),
            ]));
            $this->assertArrayContainsInKey(
                $response['results'],
                'alias',
                $problemDataPrivate['request']['problem_alias']
            );
        }

        // Expect public problem only
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
        ]));
        $this->assertArrayContainsInKey(
            $response['results'],
            'alias',
            $problemDataPublic['request']['problem_alias']
        );
    }

    /**
     * Test 'page', 'order_by' and 'sort_order' parametes of the apiList() method, and search by title.
     */
    public function testProblemListPager() {
        // Create a user and some problems with submissions for the tests.
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        for ($i = 0; $i < 6; $i++) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'promoted'
            ]));
            $runs = $i / 2;
            for ($r = 0; $r < $runs; $r++) {
                $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                    $problemData[$i],
                    $identity
                );
                $points = rand(0, 100);
                $verdict = 'WA';
                if ($points > 0) {
                    $verdict = ($points == 100) ? 'AC' : 'PA';
                }

                \OmegaUp\Test\Factories\Run::gradeRun(
                    $runData,
                    $points / 100,
                    $verdict
                );
            }
        }

        $pageSize = 2;

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        // Test search by title
        $titles = [];
        foreach ($response['results'] as $problem) {
            array_push($titles, $problem['title']);
        }
        foreach ($titles as $title) {
            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'rowcount' => $pageSize,
                'query' => $title,
            ]));
            $this->assertTrue(count($response['results']) == 1);
            $this->assertTrue($title === $response['results'][0]['title']);
        }

        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'rowcount' => $pageSize,
        ]));
        $total = $response['total'];
        $pages = intval(($total + $pageSize - 1) / $pageSize);

        // The following tests will try the different scenarios that can occur
        // with the additions of the three features to apiList(), that is, paging,
        // order by column and order mode: Call apiList() with and without
        // pagination, for each allowed ordering and each possible order mode.
        $sortOrderTypes = ['asc', 'desc'];
        $columns = ['title', 'quality', 'difficulty', 'ratio', 'points', 'score'];
        $counter = 0;
        for ($paging = 0; $paging <= 1; $paging++) {
            foreach ($columns as $col) {
                foreach ($sortOrderTypes as $sortOrder) {
                    $first = null;
                    $last = null;
                    if ($paging == 1) {
                        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                            'auth_token' => $login->auth_token,
                            'rowcount' => $pageSize,
                            'sort_order' => $sortOrder,
                            'order_by' => $col,
                            'page' => 1,
                        ]));
                        $first = $response['results'];
                        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                            'auth_token' => $login->auth_token,
                            'rowcount' => $pageSize,
                            'sort_order' => $sortOrder,
                            'order_by' => $col,
                            'page' => $pages,
                        ]));
                        $last = $response['results'];

                        // Test number of problems per page
                        $this->assertEquals($pageSize, count($first));
                    } else {
                        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                            'auth_token' => $login->auth_token,
                            'rowcount' => $pageSize,
                            'sort_order' => $sortOrder,
                            'order_by' => $col,
                        ]));
                        $first = $response['results'];
                        $last = $first;
                    }

                    $i = 0;
                    $j = count($last) - 1;
                    if ($col === 'title') {
                        $comp = strcmp($first[$i]['title'], $last[$j]['title']);
                        if ($sortOrder === 'asc') {
                            $this->assertTrue($comp <= 0);
                        } else {
                            $this->assertTrue($comp >= 0);
                        }
                    } elseif ($col === 'difficulty' || $col === 'quality') {
                        if ($sortOrder === 'asc') {
                            $this->assertGreaterThanOrEqual(
                                $last[$j][$col],
                                $first[$i][$col]
                            );
                        } else {
                            // Problems with null difficulty / quality are
                            // sorted later.
                            if (!is_null($last[$j][$col])) {
                                $this->assertLessThanOrEqual(
                                    $last[$j][$col],
                                    $first[$i][$col]
                                );
                            } else {
                                $this->assertGreaterThanOrEqual(
                                    $last[$j][$col],
                                    $first[$i][$col]
                                );
                            }
                        }
                    } else {
                        if ($sortOrder === 'asc') {
                            $this->assertTrue(
                                $first[$i][$col] <= $last[$j][$col]
                            );
                        } else {
                            $this->assertTrue(
                                $first[$i][$col] >= $last[$j][$col]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Test getting the list of Problems unsolved by some user.
     */
    public function testListUnsolvedProblemsByUser() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        /* Five different problems, each variable has its expected final verdict as suffix */
        $problemDataAC = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemDataAC2 = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemDataWA = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemDataPE = \OmegaUp\Test\Factories\Problem::createProblem();

        /*----------------- Different runs for each problem -----------------*/
        // problemDataWA will have only one run with a WA verdict
        $runDataWA = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataWA,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataWA, '0.0', 'WA');

        // problemDataAC will have two AC runs
        $runDataAC1 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataAC,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataAC1);
        $runDataAC1_2 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataAC,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataAC1_2);

        // problemDataAC2 will have three runs, one AC, one PE and one TLE
        $runDataAC2_1 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataAC2,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataAC2_1, '0.05', 'PE');
        $runDataAC2_2 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataAC2,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataAC2_2, '0.04', 'TLE');
        $runDataAC2_3 = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataAC2,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataAC2_3);

        // problemDataPE will have two runs, one with a PE verdict and  the other with a TLE verdict.
        $runDataPE = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataPE,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataPE, '0.10', 'PE');
        $runDataTLE = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemDataPE,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runDataTLE, '0.10', 'TLE');

        // Pass the user user_id (necessary for the search) and the username necessary for the UN-authentication.
        $response = \OmegaUp\Controllers\User::apiListUnsolvedProblems(new \OmegaUp\Request([
            'user_id' => $user->user_id,
            'username' => $identity->username,
        ]));

        /* -------- VALIDATE RESULTS -------*/

        // Expected to have only two problems as response although one same problem hasn't been accepted two times.
        $this->assertEquals(
            count(
                $response['problems']
            ),
            2,
            'Expected to have only 2 problems as response.'
        );

        foreach ($response['problems'] as $responseProblem) {
            // The title should match one of the non accepted problems's titles.
            switch ($responseProblem['title']) {
                case $problemDataAC['problem']->title:
                case $problemDataAC2['problem']->title:
                    $this->fail('Expected to see a non ACCEPTED problem.');
                    break;

                case $problemDataPE['problem']->title:
                    $this->assertEquals(
                        $responseProblem['title'],
                        $problemDataPE['problem']->title
                    );
                    break;

                case $problemDataWA['problem']->title:
                    $this->assertEquals(
                        $responseProblem['title'],
                        $problemDataWA['problem']->title
                    );
                    break;

                default:
                    $this->fail(
                        'Expected to see only problems tried (but not solved) by the user.'
                    );
            }
        }
    }

    /**
     * Test problem list from url, getting all the parameters
     */
    public function testProblemListPagerFromUrl() {
        // Create a user and some problems with submissions for the tests.
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $pageSize = 2;
        // Call apiList to define the number of problems and pages
        $apiListResponse = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'rowcount' => 1000,
        ]));
        $total = $apiListResponse['total'];
        $pages = intval(($total + $pageSize - 1) / $pageSize);

        // Fetching every page to get all the problems, starting on page 1
        $problems = [];
        $requestParams = [];
        for ($i = 1; $i <= $pages; $i++) {
            $response = \OmegaUp\Controllers\Problem::getProblemListForSmarty(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'rowcount' => 1000,
                    'page' => $i,
                ] + $requestParams)
            )['smartyProperties']['payload'];
            foreach ($response['problems'] as $problem) {
                $this->assertArrayHasKey(
                    'problem_id',
                    $problem,
                    'Array should contain problem_id'
                );
                $problems[] = $problem['alias'];
            }
            $nextPage = end($response['pagerItems']);
            if ($nextPage['page'] === 0) {
                continue;
            }
            $nextPageURL = $nextPage['url'];
            $nextPageURLQuery = parse_url($nextPageURL);
            // Getting all the parameters gotten by the url, even if some of them are empty
            if (isset($nextPageURLQuery['query'])) {
                parse_str($nextPageURLQuery['query'], $params);
                foreach ($params as $param => $value) {
                    $requestParams[$param] = $value;
                }
            }
        }
        // Asserting the number of non-repeated problems is the same than the total
        $this->assertEquals(
            count(
                array_unique(
                    $problems
                )
            ),
            count(
                $apiListResponse['results']
            )
        );
    }

    /**
     * Gets the list of quality problems
     */
    public function testQualityProblemList() {
        $n = 3;
        foreach (range(0, $n - 1) as $i) {
            $problemData[$i] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        $problemData[] = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'problem_level' => 'problemLevelBasicKarel',
            ])
        );

        $reviewerLogin = self::login(
            \OmegaUp\Test\Factories\QualityNomination::$reviewers[0]
        );
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData[0]['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => true,
                'tag' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => ['problemTagBitManipulation', 'problemTagRecursion'],
            ]),
        ]));
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData[1]['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => true,
                'tag' => 'problemLevelBasicIntroductionToProgramming',
                'tags' => ['problemTagMatrices', 'problemTagNumberTheory'],
            ]),
        ]));
        \OmegaUp\Controllers\QualityNomination::apiCreate(new \OmegaUp\Request([
            'auth_token' => $reviewerLogin->auth_token,
            'problem_alias' => $problemData[3]['request']['problem_alias'],
            'nomination' => 'quality_tag',
            'contents' => json_encode([
                'quality_seal' => true,
                'tag' => 'problemLevelBasicKarel',
                'tags' => ['problemTagMatrices', 'problemTagNumberTheory'],
            ]),
        ]));

        \OmegaUp\Test\Utils::runAggregateFeedback();

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Problem::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'only_quality_seal' => true,
            ])
        );

        $this->assertTrue($response['results'][0]['quality_seal']);
        $this->assertTrue($response['results'][1]['quality_seal']);
        $this->assertTrue($response['results'][2]['quality_seal']);

        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'collection_type' => 'problemLevelBasicIntroductionToProgramming',
            ])
        )['smartyProperties']['payload']['problems'];

        foreach ($result as $problem) {
            $this->assertEquals(
                $problem['tags'][0]['name'],
                'problemLevelBasicIntroductionToProgramming'
            );
        }

        $result = \OmegaUp\Controllers\Problem::getCollectionsDetailsForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'collection_type' => 'problemLevelBasicKarel',
            ])
        )['smartyProperties']['payload']['problems'];

        $this->assertEquals(
            $result[0]['tags'][0]['name'],
            'problemLevelBasicKarel'
        );
    }
}
