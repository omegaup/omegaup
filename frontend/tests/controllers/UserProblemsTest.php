<?php

/**
 * Description of UserProblemsTest
 *
 * @author joemmanuel
 */
class UserProblemsTest extends OmegaupTestCase {
    public function testEditableProblems() {
        $author = UserFactory::createUser();

        $problemData[0] = ProblemsFactory::createProblemWithAuthor($author);
        $problemData[1] = ProblemsFactory::createProblemWithAuthor($author);
        $problemData[2] = ProblemsFactory::createProblemWithAuthor($author);

        // Call API
        $login = self::login($author);
        $r = new Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = ProblemController::apiMyList($r);

        $this->assertEquals(count($problemData), count($response['problems']));
        $this->assertEquals($problemData[2]['request']['problem_alias'], $response['problems'][0]['alias']);
        $this->assertEquals($problemData[1]['request']['problem_alias'], $response['problems'][1]['alias']);
        $this->assertEquals($problemData[0]['request']['problem_alias'], $response['problems'][2]['alias']);
    }

    public function testNoProblems() {
        $author = UserFactory::createUser();

        // Call API
        $login = self::login($author);
        $r = new Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = ProblemController::apiMyList($r);

        $this->assertEquals(0, count($response['problems']));
    }

    /**
     * Test getting list of problems where the user is the admin
     */
    public function testAdminList() {
        // Our author
        $author = UserFactory::createUser();
        $problemAdminData = [];

        // Get two problems with another author, add $author to their
        // admin list
        $problemAdminData[0] = ProblemsFactory::createProblem();
        ProblemsFactory::addAdminUser($problemAdminData[0], $author);

        // Get two problems with another author, add $author to their
        // group admin list
        $problemAdminData[1] = ProblemsFactory::createProblem();
        $group = GroupsFactory::createGroup($problemAdminData[1]['author']);
        GroupsFactory::addUserToGroup($group, $author);
        ProblemsFactory::addGroupAdmin($problemAdminData[1], $group['group']);

        $problemAuthorData[0] = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $author
        ]));
        $problemAuthorData[1] = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0,
            'author' => $author
        ]));

        // Call api
        $login = self::login($author);
        $r = new Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = ProblemController::apiAdminList($r);

        // Problems should come ordered by problem id desc
        $this->assertEquals(count($problemAuthorData) + count($problemAdminData), count($response['problems']));
        $this->assertEquals($problemAuthorData[1]['request']['problem_alias'], $response['problems'][0]['alias']);
        $this->assertEquals($problemAuthorData[0]['request']['problem_alias'], $response['problems'][1]['alias']);
        $this->assertEquals($problemAdminData[1]['request']['problem_alias'], $response['problems'][2]['alias']);
        $this->assertEquals($problemAdminData[0]['request']['problem_alias'], $response['problems'][3]['alias']);
    }

    /**
     * Test ProblemsDAO::getPrivateCount when there's 1 private problem
     */
    public function testPrivateProblemsCount() {
        // Create private problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0
        ]));
        $user = $problemData['author'];

        $this->assertEquals(1, ProblemsDAO::getPrivateCount($user));
    }

    /**
     * Test ProblemsDAO::getPrivateCount when there's 1 public problem
     */
    public function testPrivateProblemsCountWithPublicProblem() {
        // Create public problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1
        ]));
        $user = $problemData['author'];

        $this->assertEquals(0, ProblemsDAO::getPrivateCount($user));
    }

    /**
     * Test ProblemsDAO::getPrivateCount when there's 0 problems
     */
    public function testPrivateProblemsCountWithNoProblems() {
        $user = UserFactory::createUser();

        $this->assertEquals(0, ProblemsDAO::getPrivateCount($user));
    }
}
