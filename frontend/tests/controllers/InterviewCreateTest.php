<?php

/**
 * @author alanboy
 */
class InterviewCreateTest extends OmegaupTestCase {
    public function testCreateAndListInterview() {
        $interviewer = UserFactory::createInterviewerUser();

        // Verify I started with nothing
        $interviews = InterviewsDAO::getMyInterviews($interviewer->user_id);
        $this->assertEquals(0, count($interviews));

        $login = self::login($interviewer);
        $response = InterviewController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'title' => 'My first interview',
            'alias' => 'my-first-interview',
            'duration' => 60,
        ]));

        $this->assertEquals('ok', $response['status']);

        $interviews = InterviewsDAO::getMyInterviews($interviewer->user_id);

        // Must have 1 interview
        $this->assertEquals(1, count($interviews));
    }

    public function testInterviewsMustBePrivate() {
        $contestant = UserFactory::createInterviewerUser();

        $login = self::login($contestant);
        $response = InterviewController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'title' => 'My second interview',
            'alias' => 'my-second-interview',
            'duration' => 60,
        ]));

        $interview = InterviewsDAO::getMyInterviews($contestant->user_id);

        $this->assertEquals(true, $interview[0]['interview']);
        $this->assertEquals(0, $interview[0]['public']);
        $this->assertEquals(0, $interview[0]['contestant_must_register'], 'Interviews must have the contestant_must_register property');
    }

    public function testAddUsersToInterview() {
        // Create an interviewer
        $interviewer = UserFactory::createInterviewerUser();

        $login = self::login($interviewer);
        $interviewAlias = 'my-third-interview';
        $response = InterviewController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'title' => 'My third interview',
            'alias' => $interviewAlias,
            'duration' => 60,
        ]));

        $this->assertEquals('ok', $response['status']);

        // add 2 new users via email (not existing omegaupusers)
        $email1 = Utils::CreateRandomString() . 'a@foobar.net';
        $email2 = Utils::CreateRandomString() . 'b@foobar.net';

        $response = InterviewController::apiAddUsers(new Request([
            'auth_token' => $login->auth_token,
            'interview_alias' => $interviewAlias,
            'usernameOrEmailsCSV' => $email1 . ',' . $email2,
        ]));
        $this->assertEquals('ok', $response['status']);

        $this->assertNotNull($createdUser1 = UsersDAO::FindByEmail($email1), 'user should have been created by adding email to interview');
        $this->assertNotNull(UsersDAO::FindByEmail($email2), 'user should have been created by adding email to interview');

        $this->assertEquals($createdUser1->verified, 0, 'new created users should not be email-validated');

        // add 2 users that are already omegaup users (using registered email)
        $emailFor1 = Utils::CreateRandomString().'@mail.com';
        $interviewee1 = UserFactory::createUser(null, null, $emailFor1);

        $emailFor2 = Utils::CreateRandomString().'@mail.com';
        $interviewee2 = UserFactory::createUser(null, null, $emailFor2);

        $response = InterviewController::apiAddUsers(new Request([
            'auth_token' => $login->auth_token,
            'interview_alias' => $interviewAlias,
            'usernameOrEmailsCSV' => $emailFor1 . ',' . $emailFor2,
        ]));
        $this->assertEquals('ok', $response['status']);

        // add 2 users that are already omegaup users (using registered username)
        $interviewee3 = UserFactory::createUser();
        $interviewee4 = UserFactory::createUser();

        $response = InterviewController::apiAddUsers(new Request([
            'auth_token' => $login->auth_token,
            'interview_alias' => $interviewAlias,
            'usernameOrEmailsCSV' => $interviewee3->username . ',' . $interviewee4->username,
        ]));
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

        $login = self::login($interviewer);
        $response = InterviewController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'title' => 'My fourth interview',
            'alias' => 'my-fourth-interview',
            'duration' => 60,
        ]));
    }
}
