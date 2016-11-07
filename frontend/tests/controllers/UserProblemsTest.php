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
        // Call api
        $r = new Request(array(
            'auth_token' => self::login($author)
        ));
        $response = ProblemController::apiMyList($r);

        $this->assertEquals(count($problemData), count($response['problems']));
        $this->assertEquals($problemData[2]['request']['alias'], $response['problems'][0]['alias']);
        $this->assertEquals($problemData[1]['request']['alias'], $response['problems'][1]['alias']);
        $this->assertEquals($problemData[0]['request']['alias'], $response['problems'][2]['alias']);
    }

    public function testNoProblems() {
        $author = UserFactory::createUser();

        // Call API
        // Call api
        $r = new Request(array(
            'auth_token' => self::login($author)
        ));
        $response = ProblemController::apiMyList($r);

        $this->assertEquals(0, count($response['problems']));
    }

    /**
     * Test getting list of problems where the user is the admin
     */
    public function testAdminList() {
        // Our author
        $author = UserFactory::createUser();
        $problemAdminData = array();

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

        $problemAuthorData[0] = ProblemsFactory::createProblem(null /*zipName*/, null /*title*/, 1 /*public*/, $author);
        $problemAuthorData[1] = ProblemsFactory::createProblem(null /*zipName*/, null /*title*/, 0 /*public*/, $author);

        // Call api
        $login = self::login($author);
        $r = new Request(array(
            'auth_token' => $login->auth_token,
        ));
        $response = ProblemController::apiAdminList($r);

        // Problems should come ordered by problem id desc
        $this->assertEquals(count($problemAuthorData) + count($problemAdminData), count($response['problems']));
        $this->assertEquals($problemAuthorData[1]['request']['alias'], $response['problems'][0]['alias']);
        $this->assertEquals($problemAuthorData[0]['request']['alias'], $response['problems'][1]['alias']);
        $this->assertEquals($problemAdminData[1]['request']['alias'], $response['problems'][2]['alias']);
        $this->assertEquals($problemAdminData[0]['request']['alias'], $response['problems'][3]['alias']);
    }

    /**
     * Test ProblemsDAO::getPrivateCount when there's 1 private problem
     */
    public function testPrivateProblemsCount() {
        // Create private problem
        $problemData = ProblemsFactory::createProblem(null, null, 0 /*public*/);
        $user = $problemData['author'];

        $this->assertEquals(1, ProblemsDAO::getPrivateCount($user));
    }

    /**
     * Test ProblemsDAO::getPrivateCount when there's 1 public problem
     */
    public function testPrivateProblemsCountWithPublicProblem() {
        // Create public problem
        $problemData = ProblemsFactory::createProblem(null, null, 1 /*public*/);
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
