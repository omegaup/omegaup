<?php

/**
 * Description of ProblemEditUITest
 *
 * @author joemmanuel
 */
class ProblemEditUITest extends OmegaupUITestCase {
    public function testEditProblem() {
        // Login
        $author = $this->createUserAndLogin();

        // Create a problem
        $problemData = ProblemsFactory::createProblem(null, null, 1, $author);

        // Open problem create
        $this->open('/problemedit.php');

        sleep(1);

        $this->type('name=edit-problem-list', $problemData['request']['alias']);

        $this->waitForValue('name=title', $problemData['request']['title']);

        $problemNewData = ProblemsFactory::getRequest();

        $this->type('name=title', $problemNewData['request']['title']);
        $this->type('source', $problemNewData['request']['source']);
        $this->type('time_limit', '666');
        $this->type('memory_limit', '1234');
        $this->type('validator', 'token-caseless');
        $this->type('public', '1');

        // Click inicia sesion
        $this->clickAndWait("//input[@value='Actualizar problema']");

        $this->assertElementContainsText('//*[@id="content"]/div[2]/div', 'Problem updated succesfully!');

        // Verify data in DB
        $problem_mask = new Problems();
        $problem_mask->title = $problemNewData['request']['title'];
        $problems = ProblemsDAO::search($problem_mask);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));

        $this->assertEquals($problemNewData['request']['source'], $problems[0]->source);
        $this->assertEquals(666, $problems[0]->time_limit);
        $this->assertEquals(1234, $problems[0]->memory_limit);
        $this->assertEquals('token-caseless', $problems[0]->validator);
        $this->assertEquals('1', $problems[0]->public);
    }
}
