<?php

/**
 * @author alanboy
 */
class InterviewCreateTest extends OmegaupTestCase {
    public function testCreateAndListInterview() {
        $r = new Request();

        $interviewer = UserFactory::createInterviewerUser();

        // Verify I started with nothing
        $interviews = ContestsDAO::getMyInterviews($interviewer->user_id);
        $this->assertEquals(0, count($interviews));

        $r['auth_token'] = $this->login($interviewer);
        $r['title'] = 'My second interview';
        $r['alias'] = 'my-first-interview';
        $r['duration'] = 60;

        $response = InterviewController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);

        $interviews = ContestsDAO::getMyInterviews($interviewer->user_id);

        // Must have 1 interview
        $this->assertEquals(1, count($interviews));
    }

    public function testInterviewsMustBePrivate() {
        $r = new Request();

        $contestant = UserFactory::createInterviewerUser();

        $r['auth_token'] = $this->login($contestant);
        $r['title'] = 'My second interview';
        $r['alias'] = 'my-second-interview';
        $r['duration'] = 60;

        $response = InterviewController::apiCreate($r);

        $interview = ContestsDAO::getMyInterviews($contestant->user_id);

        $this->assertEquals(true, $interview[0]['interview']);
        $this->assertEquals(0, $interview[0]['public']);
        $this->assertEquals(0, $interview[0]['contestant_must_register'], 'Interviews must have the contestant_must_register property');
    }

    public function testAddUsersToInterview() {
        $r = new Request();

        // Create an interview
        $interviewer = UserFactory::createInterviewerUser();

        $r['auth_token'] = $this->login($interviewer);
        $r['title'] = 'My third interview';
        $r['alias'] = 'my-third-interview';
        $r['duration'] = 60;

        $response = InterviewController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);

        // add 2 new users via email (not existing omegaupusers)
        $email1 = Utils::CreateRandomString() . 'a@foobar.net';
        $email2 = Utils::CreateRandomString() . 'b@foobar.net';

        $r1 = new Request();
        $r1['auth_token'] = $this->login($interviewer);
        $r1['interview_alias'] = $r['alias'];
        $r1['usernameOrEmailsCSV'] = $email1 . ',' . $email2;

        $response = InterviewController::apiAddUsers($r1);
        $this->assertEquals('ok', $response['status']);

        $this->assertNotNull($createdUser1 = UsersDAO::FindByEmail($email1), 'user should have been created by adding email to interview');
        $this->assertNotNull(UsersDAO::FindByEmail($email2), 'user should have been created by adding email to interview');

        $this->assertEquals($createdUser1->getVerified(), 0, 'new created users should not be email-validated');

        // add 2 users that are already omegaup users (using registered email)
        $emailFor1 = Utils::CreateRandomString().'@mail.com';
        $interviewee1 = UserFactory::createUser(null, null, $emailFor1);

        $emailFor2 = Utils::CreateRandomString().'@mail.com';
        $interviewee2 = UserFactory::createUser(null, null, $emailFor2);

        $r2 = new Request();
        $r2['auth_token'] = $this->login($interviewer);
        $r2['interview_alias'] = $r['alias'];
        $r2['usernameOrEmailsCSV'] = $emailFor1 . ',' . $emailFor2;

        $response = InterviewController::apiAddUsers($r2);
        $this->assertEquals('ok', $response['status']);

        // add 2 users that are already omegaup users (using registered username)
        $interviewee3 = UserFactory::createUser();
        $interviewee4 = UserFactory::createUser();

        $r3 = new Request();
        $r3['auth_token'] = $this->login($interviewer);
        $r3['interview_alias'] = $r['alias'];
        $r3['usernameOrEmailsCSV'] = $interviewee3->getUsername() . ',' . $interviewee4->getUsername();

        $response = InterviewController::apiAddUsers($r3);
        $this->assertEquals('ok', $response['status']);
    }

    /**
     *
     * Only site-admins and interviewers can create interviews for now
     *
     * @expectedException ForbiddenAccessException
     */
    public function testOnlyInterviewersCanCreateInterviews() {
        $r = new Request();

        // Create an interview
        $interviewer = UserFactory::createUser();

        $r['auth_token'] = $this->login($interviewer);
        $r['title'] = 'My 4th interview';
        $r['alias'] = 'my-4-interview';
        $r['duration'] = 60;

        $response = InterviewController::apiCreate($r);
    }
}
