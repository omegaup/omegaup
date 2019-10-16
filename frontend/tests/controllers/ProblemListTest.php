<?php
/**
 * Description of ProblemList
 *
 * @author joemmanuel
 */
class ProblemList extends OmegaupTestCase {
    /**
     * Gets the list of problems
     */
    public function testProblemList() {
        // Get 3 problems
        $n = 3;
        for ($i = 0; $i < $n; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED
            ]));
        }

        // Get 1 problem private, should not appear
        $privateProblemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE
        ]));

        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($user);
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        // Check that all public problems are there
        for ($i = 0; $i < $n; $i++) {
            $count = 0;
            foreach ($response['results'] as $problemResponse) {
                if ($problemResponse === 'ok') {
                    continue;
                }

                if ($problemResponse['alias'] === $problemData[$i]['request']['problem_alias']) {
                    $count++;
                }
            }
            if ($count != 1) {
                $this->fail(
                    'Problem' . $problemData[$i]['request']['alias'] . ' is not exactly once.'
                );
            }
        }

        // Check private problem is not there
        $exists = false;
        foreach ($response['results'] as $problemResponse) {
            if ($problemResponse['alias'] === $privateProblemData['request']['problem_alias']) {
                $exists = true;
                break;
            }
            // Check if quality_histogram and difficulty_histogram fields are being returned also
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
            $this->fail(
                'Private problem' . $privateProblemData['request']['problem_alias'] . ' is in the list.'
            );
        }
    }

    /**
     * Test getting a list of problems while filtering by tag.
     */
    public function testProblemListWithTags() {
        // Get 3 problems
        $n = 3;
        for ($i = 0; $i < $n; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED
            ]));
            for ($j = 0; $j <= $i; $j++) {
                ProblemsFactory::addTag(
                    $problemData[$i],
                    "tag-$j",
                    1 /* public */
                );
            }
        }

        // Get 1 problem private, should not appear
        $privateProblemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE
        ]));
        for ($j = 0; $j < $n; $j++) {
            ProblemsFactory::addTag(
                $privateProblemData,
                "tag-$j",
                1 /* public */
            );
        }

        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($user);

        // Test one tag at a time
        for ($j = 0; $j < $n; $j++) {
            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => "tag-$j",
            ]));
            $this->assertEquals($response['status'], 'ok');
            // $n public problems but not the private problem that has all tags.
            // But only problems $j or later have tag $j.
            $this->assertCount($n - $j, $response['results']);
        }

        // Test multiple tags at a time
        $tags = [];
        for ($j = 0; $j < $n; $j++) {
            $tags[] = "tag-$j";

            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => $tags,
            ]));
            $this->assertEquals($response['status'], 'ok');
            // $n public problems but not the private problem that has all tags.
            // But only problems $j or later have tags 0 through $j.
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
        $karel_problem = 'kj,kp,cpp,c'; // Karel problems should allow kj AND kp extensions
        for ($i = 0; $i < $n; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED,
                'languages' => $i % 2 == 0 ? $karel_problem : 'kj,cpp,c',
            ]));
            for ($j = 0; $j <= $i; $j++) {
                ProblemsFactory::addTag(
                    $problemData[$i],
                    "test-tag-$j",
                    1 /* public */
                );
            }
        }

        // 5 users are going to be created, each i user will solve i problems
        // and all users are going to send their feedback to the first problem
        $users = [];
        $identities = [];
        for ($i = 0; $i < 5; $i++) {
            ['user' => $users[$i], 'identity' => $identities[$i]] = UserFactory::createUser();
            for ($j = 0; $j <= $i; $j++) {
                $runData = RunsFactory::createRunToProblem(
                    $problemData[$j],
                    $users[$i]
                );
                RunsFactory::gradeRun($runData);
            }
            $login[] = self::login($users[$i]);
        }

        QualityNominationFactory::createSuggestion(
            $login[0],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            3, // quality
            [],
            false
        );

        QualityNominationFactory::createSuggestion(
            $login[1],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            4, // quality
            [],
            false
        );

        QualityNominationFactory::createSuggestion(
            $login[2],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            2, // quality
            [],
            false
        );

        QualityNominationFactory::createSuggestion(
            $login[3],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            4, // quality
            [],
            false
        );

        QualityNominationFactory::createSuggestion(
            $login[4],
            $problemData[0]['request']['problem_alias'],
            4, // difficulty
            4, // quality
            [],
            false
        );

        Utils::RunAggregateFeedback();

        // Filter 1:
        // - Containing tag-0
        // - Only Karel problems
        // - Difficulty between 0 and 3
        // - Sorted by popularity
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $login[0]->auth_token,
            'tag' => 'test-tag-0',
            'programming_languages' => ['kp', 'kj'],
            'difficulty_range' => [0,2],
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
            'tag' => ['test-tag-0', 'test-tag-3'],
            'require_all_tags' => false,
            'difficulty_range' => [0,4],
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
            'tag' => ['test-tag-2', 'test-tag-3'],
            'require_all_tags' => false,
            'difficulty_range' => [1,4],
            'order_by' => 'quality',
        ]));
        $this->assertCount(0, $response['results']);
    }

    /**
     * Tests problem lists when searching by tag when tags are not public.
     */
    public function testProblemListWithPrivateTags() {
        [
            'user' => $admin,
            'identity' => $identityAdmin
        ] = UserFactory::createAdminUser(
            new UserParams(
                ['username' => 'admin']
            )
        );
        ['user' => $userA, 'identity' => $identityA] = UserFactory::createUser(
            new UserParams(
                ['username' => 'user_a']
            )
        );
        ['user' => $userB, 'identity' => $identityB] = UserFactory::createUser(
            new UserParams(
                ['username' => 'user_b']
            )
        );
        ['user' => $otherUser, 'identity' => $otherIdentity] = UserFactory::createUser(
            new UserParams(
                ['username' => 'other']
            )
        );

        $problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED,
            'author' => $admin
        ]));
        $private_problem = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $admin
        ]));
        $problem_a = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED,
            'author' => $userA
        ]));
        $private_problem_a = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $userA
        ]));
        $problem_b = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED,
            'author' => $userB
        ]));
        $private_problem_b = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $userB
        ]));

        $all_problems = [$problem, $private_problem,
                         $problem_a, $private_problem_a, $problem_b, $private_problem_b];
        // Tag each problem with 3 tags, 2 public and 1 private.
        foreach ($all_problems as $problem) {
            ProblemsFactory::addTag($problem, 'a', 1 /* public */);
            ProblemsFactory::addTag($problem, 'b', 1 /* public */);
            ProblemsFactory::addTag($problem, 'c', 0 /* public */);
        }

        $all_users = [$admin, $userA, $userB, $otherUser];
        $tag_a_results = [
            6,      // admin user can see all 6 problems
            4,      // User A sees 3 public problems and private_problem_a
            4,      // User B sees 3 public problems and private_problem_b
            3,      // Random user sees only 3 public problems
        ];
        // Same thing when searching for tags "a" and "b", since tags a and b are public
        $tag_ab_results = $tag_a_results;
        // But searching for tags "a" and "c" won't give other users' problems
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
                'tag' => 'a',
            ]));
            $this->assertEquals($response['status'], 'ok');
            $this->assertCount($tag_a_results[$i], $response['results']);

            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => ['a', 'b']
            ]));
            $this->assertEquals($response['status'], 'ok');
            $this->assertCount($tag_ab_results[$i], $response['results']);

            $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'tag' => ['a', 'c']
            ]));
            $this->assertEquals($response['status'], 'ok');
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
            $problemData[$i] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED
            ]));
        }

        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($user);
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
        ['user' => $author, 'identity' => $identity] = UserFactory::createUser();
        ['user' => $anotherAuthor, 'identity' => $anotherIdentity] = UserFactory::createUser();

        $problemDataPublic = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED,
            'author' => $author
        ]));
        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $author
        ]));
        $anotherProblemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $anotherAuthor
        ]));

        $login = self::login($author);
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
        ['user' => $author, 'identity' => $identity] = UserFactory::createUser();

        $problemDataPublic = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED,
            'author' => $author
        ]));
        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $author
        ]));

        ['user' => $admin, 'identity' => $identityAdmin] = UserFactory::createAdminUser();

        $login = self::login($admin);
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
        ['user' => $author, 'identity' => $identity] = UserFactory::createUser();

        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $author
        ]));

        ['user' => $addedAdmin, 'identity' => $addedIdentityAdmin] = UserFactory::createUser();

        $adminLogin = self::login($addedAdmin);
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

        $login = self::login($author);
        $response = \OmegaUp\Controllers\Problem::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemDataPrivate['request']['problem_alias'],
            'usernameOrEmail' => $addedAdmin->username,
        ]));

        $this->assertEquals('ok', $response['status']);

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
        ['user' => $author, 'identity' => $identity] = UserFactory::createUser();

        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $author
        ]));
        $alias = $problemDataPrivate['request']['problem_alias'];

        ['user' => $addedAdmin, 'identity' => $addedIdentityAdmin] = UserFactory::createUser();

        $login = self::login($addedAdmin);
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

        $authorLogin = self::login($author);
        $group = GroupsFactory::createGroup(
            $author,
            null,
            null,
            null,
            $authorLogin
        );
        GroupsFactory::addUserToGroup($group, $addedAdmin, $authorLogin);
        GroupsFactory::addUserToGroup($group, $author, $authorLogin);

        $response = \OmegaUp\Controllers\Problem::apiAddGroupAdmin(new \OmegaUp\Request([
            'auth_token' => $authorLogin->auth_token,
            'problem_alias' => $problemDataPrivate['request']['problem_alias'],
            'group' => $group['group']->alias,
        ]));

        $this->assertEquals('ok', $response['status']);

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
        ['user' => $author, 'identity' => $identity] = UserFactory::createUser();

        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE,
            'author' => $author
        ]));
        $alias = $problemDataPrivate['request']['problem_alias'];

        $authorLogin = self::login($author);
        $group = GroupsFactory::createGroup(
            $author,
            null,
            null,
            null,
            $authorLogin
        );
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        GroupsFactory::addUserToGroup(
            $group,
            $user,
            $authorLogin
        );
        GroupsFactory::addUserToGroup($group, $author, $authorLogin);

        $response = \OmegaUp\Controllers\Problem::apiAddGroupAdmin(new \OmegaUp\Request([
            'auth_token' => $authorLogin->auth_token,
            'problem_alias' => $problemDataPrivate['request']['problem_alias'],
            'group' => $group['group']->alias,
        ]));

        $this->assertEquals('ok', $response['status']);

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
        ['user' => $author, 'identity' => $authorIdentity] = UserFactory::createUser();
        ['user' => $helper, 'identity' => $helperIdentity] = UserFactory::createUser();

        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED,
            'author' => $author
        ]));

        $group = GroupsFactory::createGroup($author);
        GroupsFactory::addUserToGroup($group, $helper);

        $login = self::login($author);
        $response = \OmegaUp\Controllers\Problem::apiAddGroupAdmin(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemDataPrivate['request']['problem_alias'],
            'group' => $group['group']->alias,
        ]));

        $this->assertEquals('ok', $response['status']);

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
        ['user' => $author, 'identity' => $identity] = UserFactory::createUser();
        $n = 3;
        for ($i = 0; $i < $n; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED,
                'author' => $author
            ]));
        }

        $login = self::login($author);
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
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        $problemData = ProblemsFactory::createProblem();
        $problemDataNoRun = ProblemsFactory::createProblem();
        $problemDataDecimal = ProblemsFactory::createProblem();

        $runData = RunsFactory::createRunToProblem($problemData, $contestant);
        RunsFactory::gradeRun($runData);

        $runDataDecimal = RunsFactory::createRunToProblem(
            $problemDataDecimal,
            $contestant
        );
        RunsFactory::gradeRun($runDataDecimal, '.123456', 'PA');

        $login = self::login($contestant);
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
        $problemData = ProblemsFactory::createProblem();

        $response = \OmegaUp\Controllers\Problem::apiList(
            new \OmegaUp\Request()
        );

        // Validate results
        foreach ($response['results'] as $responseProblem) {
            if ($responseProblem['score'] != '0') {
                $this->fail(
                    'Expecting score to be not set for non-logged in users'
                );
            }
        }
    }

    /**
     * Test List API with query param
     */
    public function testListWithAliasQuery() {
        $problemDataPublic = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED
        ]));
        $problemDataPrivate = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PRIVATE
        ]));

        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $userLogin = self::login($user);

        // Expect public problem only
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'query' => substr($problemDataPublic['request']['title'], 2, 5),
        ]));
        $this->assertArrayContainsInKey(
            $response['results'],
            'alias',
            $problemDataPublic['request']['problem_alias']
        );

        // Expect 0 problems, matches are private for $user
        $response = \OmegaUp\Controllers\Problem::apiList(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'query' => substr($problemDataPrivate['request']['title'], 2, 5),
        ]));
        $this->assertEquals(0, count($response['results']));

        // Expect 1 problem, admin can see private problem
        {
            ['user' => $admin, 'identity' => $identityAdmin] = UserFactory::createAdminUser();
            $adminLogin = self::login($admin);
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
     * Test 'page', 'order_by' and 'mode' parametes of the apiList() method, and search by title.
     */
    public function testProblemListPager() {
        // Create a user and some problems with submissions for the tests.
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();
        for ($i = 0; $i < 6; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => \OmegaUp\Controllers\Problem::VISIBILITY_PROMOTED
            ]));
            $runs = $i / 2;
            for ($r = 0; $r < $runs; $r++) {
                $runData = RunsFactory::createRunToProblem(
                    $problemData[$i],
                    $contestant
                );
                $points = rand(0, 100);
                $verdict = 'WA';
                if ($points > 0) {
                    $verdict = ($points == 100) ? 'AC' : 'PA';
                }

                RunsFactory::gradeRun($runData, $points / 100, $verdict);
            }
        }

        $login = self::login($contestant);
        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Problem::apiList($request);

        // Test search by title
        $titles = [];
        foreach ($response['results'] as $problem) {
            array_push($titles, $problem['title']);
        }
        foreach ($titles as $title) {
            $request['query'] = $title;
            $response = \OmegaUp\Controllers\Problem::apiList($request);
            $this->assertTrue(count($response['results']) == 1);
            $this->assertTrue($title === $response['results'][0]['title']);
        }

        $request['query'] = null;
        $response = \OmegaUp\Controllers\Problem::apiList($request);
        $total = $response['total'];
        $pages = intval(($total + PROBLEMS_PER_PAGE - 1) / PROBLEMS_PER_PAGE);

        // The following tests will try the different scenarios that can occur
        // with the additions of the three features to apiList(), that is, paging,
        // order by column and order mode: Call apiList() with and without
        // pagination, for each allowed ordering and each possible order mode.
        $modes = ['asc', 'desc'];
        $columns = ['title', 'quality', 'difficulty', 'ratio', 'points', 'score'];
        $counter = 0;
        for ($paging = 0; $paging <= 1; $paging++) {
            foreach ($columns as $col) {
                foreach ($modes as $mode) {
                    $first = null;
                    $last = null;
                    $request['mode'] = $mode;
                    $request['order_by'] = $col;
                    if ($paging == 1) {
                        // Clear offset and rowcount if set.
                        if (isset($request['offset'])) {
                            unset($request['offset']);
                        }
                        if (isset($request['rowcount'])) {
                            unset($request['rowcount']);
                        }
                        $request['page'] = 1;
                        $response = \OmegaUp\Controllers\Problem::apiList(
                            $request
                        );
                        $first = $response['results'];
                        $request['page'] = $pages;
                        $response = \OmegaUp\Controllers\Problem::apiList(
                            $request
                        );
                        $last = $response['results'];

                        // Test number of problems per page
                        $this->assertEquals(PROBLEMS_PER_PAGE, count($first));
                    } else {
                        $request['page'] = null;
                        $response = \OmegaUp\Controllers\Problem::apiList(
                            $request
                        );
                        $first = $response['results'];
                        $last = $first;
                    }

                    $i = 0;
                    $j = count($last) - 1;
                    if ($col === 'title') {
                        $comp = strcmp($first[$i]['title'], $last[$j]['title']);
                        if ($mode === 'asc') {
                            $this->assertTrue($comp <= 0);
                        } else {
                            $this->assertTrue($comp >= 0);
                        }
                    } else {
                        if ($mode === 'asc') {
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
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        /* Five different problems, each variable has its expected final verdict as suffix */
        $problemDataAC = ProblemsFactory::createProblem();
        $problemDataAC2 = ProblemsFactory::createProblem();
        $problemDataWA = ProblemsFactory::createProblem();
        $problemDataPE = ProblemsFactory::createProblem();

        /*----------------- Different runs for each problem -----------------*/
        // problemDataWA will have only one run with a WA verdict
        $runDataWA = RunsFactory::createRunToProblem($problemDataWA, $user);
        RunsFactory::gradeRun($runDataWA, '0.0', 'WA');

        // problemDataAC will have two AC runs
        $runDataAC1 = RunsFactory::createRunToProblem($problemDataAC, $user);
        RunsFactory::gradeRun($runDataAC1);
        $runDataAC1_2 = RunsFactory::createRunToProblem($problemDataAC, $user);
        RunsFactory::gradeRun($runDataAC1_2);

        // problemDataAC2 will have three runs, one AC, one PE and one TLE
        $runDataAC2_1 = RunsFactory::createRunToProblem($problemDataAC2, $user);
        RunsFactory::gradeRun($runDataAC2_1, '0.05', 'PE');
        $runDataAC2_2 = RunsFactory::createRunToProblem($problemDataAC2, $user);
        RunsFactory::gradeRun($runDataAC2_2, '0.04', 'TLE');
        $runDataAC2_3 = RunsFactory::createRunToProblem($problemDataAC2, $user);
        RunsFactory::gradeRun($runDataAC2_3);

        // problemDataPE will have two runs, one with a PE verdict and  the other with a TLE verdict.
        $runDataPE = RunsFactory::createRunToProblem($problemDataPE, $user);
        RunsFactory::gradeRun($runDataPE, '0.10', 'PE');
        $runDataTLE = RunsFactory::createRunToProblem($problemDataPE, $user);
        RunsFactory::gradeRun($runDataTLE, '0.10', 'TLE');

        // Pass the user user_id (necessary for the search) and the username necessary for the UN-authentication.
        $response = \OmegaUp\Controllers\User::apiListUnsolvedProblems(new \OmegaUp\Request([
            'user_id' => $user->user_id,
            'username' => $user->username,
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
}
