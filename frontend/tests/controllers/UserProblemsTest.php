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
        $response = UserController::apiProblems($r);

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
        $response = UserController::apiProblems($r);

        $this->assertEquals(0, count($response['problems']));
    }
}
