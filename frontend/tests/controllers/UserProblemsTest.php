<?php

/**
 * Description of UserProblemsTest
 *
 * @author joemmanuel
 */
class UserProblemsTest extends OmegaupTestCase {
    public function testEditableProblems() {
        ['user' => $author, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $problemData[0] = ProblemsFactory::createProblemWithAuthor($identity);
        $problemData[1] = ProblemsFactory::createProblemWithAuthor($identity);
        $problemData[2] = ProblemsFactory::createProblemWithAuthor($identity);

        // Call API
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Problem::apiMyList($r);

        $this->assertEquals(count($problemData), count($response['problems']));
        $this->assertEquals(
            $problemData[2]['request']['problem_alias'],
            $response['problems'][0]['alias']
        );
        $this->assertEquals(
            $problemData[1]['request']['problem_alias'],
            $response['problems'][1]['alias']
        );
        $this->assertEquals(
            $problemData[0]['request']['problem_alias'],
            $response['problems'][2]['alias']
        );
    }

    public function testNoProblems() {
        ['user' => $author, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Call API
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Problem::apiMyList($r);

        $this->assertEquals(0, count($response['problems']));
    }

    /**
     * Test getting list of problems where the user is the admin
     */
    public function testAdminList() {
        // Our author
        ['user' => $author, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $problemAdminData = [];

        // Get two problems with another author, add $author to their
        // admin list
        $problemAdminData[0] = ProblemsFactory::createProblem();
        ProblemsFactory::addAdminUser($problemAdminData[0], $identity);

        // Get two problems with another author, add $author to their
        // group admin list
        $problemAdminData[1] = ProblemsFactory::createProblem();
        $group = GroupsFactory::createGroup($problemAdminData[1]['author']);
        GroupsFactory::addUserToGroup($group, $identity);
        ProblemsFactory::addGroupAdmin($problemAdminData[1], $group['group']);

        $problemAuthorData[0] = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'author' => $identity
        ]));
        $problemAuthorData[1] = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0,
            'author' => $identity
        ]));

        // Call api
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Problem::apiAdminList($r);

        // Problems should come ordered by problem id desc
        $this->assertEquals(
            count(
                $problemAuthorData
            ) + count(
                $problemAdminData
            ),
            count(
                $response['problems']
            )
        );
        $this->assertEquals(
            $problemAuthorData[1]['request']['problem_alias'],
            $response['problems'][0]['alias']
        );
        $this->assertEquals(
            $problemAuthorData[0]['request']['problem_alias'],
            $response['problems'][1]['alias']
        );
        $this->assertEquals(
            $problemAdminData[1]['request']['problem_alias'],
            $response['problems'][2]['alias']
        );
        $this->assertEquals(
            $problemAdminData[0]['request']['problem_alias'],
            $response['problems'][3]['alias']
        );
    }

    /**
     * Test \OmegaUp\DAO\Problems::getPrivateCount when there's 1 private problem
     */
    public function testPrivateProblemsCount() {
        // Create private problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0
        ]));
        $user = $problemData['authorUser'];

        $this->assertEquals(1, \OmegaUp\DAO\Problems::getPrivateCount($user));
    }

    /**
     * Test \OmegaUp\DAO\Problems::getPrivateCount when there's 1 public problem
     */
    public function testPrivateProblemsCountWithPublicProblem() {
        // Create public problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1
        ]));
        $user = $problemData['authorUser'];

        $this->assertEquals(0, \OmegaUp\DAO\Problems::getPrivateCount($user));
    }

    /**
     * Test \OmegaUp\DAO\Problems::getPrivateCount when there's 0 problems
     */
    public function testPrivateProblemsCountWithNoProblems() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $this->assertEquals(0, \OmegaUp\DAO\Problems::getPrivateCount($user));
    }
}
