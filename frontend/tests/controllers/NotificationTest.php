<?php

/**
 * Tests for NotificationController
 *
 * @author carlosabcs
 */

class NotificationTest extends OmegaupTestCase {
    /**
     * Basic test for creating a problem
     */
    public function testCreateValidProblem() {
        // Get the problem data
        $problemData = ProblemsFactory::getRequest();
        $r = $problemData['request'];
        $problemAuthor = $problemData['author'];

        // Login user
        $login = self::login($problemAuthor);
        $r['auth_token'] = $login->auth_token;

        // Get File Uploader Mock and tell Omegaup API to use it
        FileHandler::SetFileUploader($this->createFileUploaderMock());

        // Call the API
        $response = ProblemController::apiCreate($r);

        // Validate
        // Verify response
        $this->assertEquals('ok', $response['status']);

        // Verify data in DB
        $problems = ProblemsDAO::getByTitle($r['title']);

        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));
        $problem = $problems[0];

        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->problem_id);

        // Verify DB data
        $this->assertEquals($r['title'], $problem->title);
        $this->assertEquals(substr($r['title'], 0, 32), $problem->alias);
        $this->assertEquals($r['order'], $problem->order);
        $this->assertEquals($r['source'], $problem->source);
        $this->assertEqualSets($r['languages'], $problem->languages);

        // Verify author username -> author id conversion
        $acl = ACLsDAO::getByPK($problem->acl_id);
        $user = UsersDAO::getByPK($acl->owner_id);
        $this->assertEquals($user->username, $r['author_username']);

        // Verify problem settings.
        $problemArtifacts = new ProblemArtifacts($r['problem_alias']);
        $problemSettings = json_decode($problemArtifacts->get('settings.json'));
        $this->assertEquals(false, $problemSettings->Slow);
        $this->assertEquals($r['validator'], $problemSettings->Validator->Name);
        $this->assertEquals(5000, $r['time_limit']);
        $this->assertEquals('5s', $problemSettings->Limits->TimeLimit);
        $this->assertEquals(
            $r['memory_limit'] * 1024,
            $problemSettings->Limits->MemoryLimit
        );

        // Verify problem contents were copied
        $this->assertTrue($problemArtifacts->exists('settings.json'));
        $this->assertTrue($problemArtifacts->exists('cases'));
        $this->assertTrue($problemArtifacts->exists('statements/en.markdown'));

        // Default data
        $this->assertEquals(0, $problem->visits);
        $this->assertEquals(0, $problem->submissions);
        $this->assertEquals(0, $problem->accepted);
        $this->assertEquals(0, $problem->difficulty);
    }
}
