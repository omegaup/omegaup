<?php

/**
 * @author alanboy
 */
class InterviewCreateTest extends OmegaupTestCase {
    public function testCreateAndListInterview() {
        ['user' => $interviewer, 'identity' => $identity] = UserFactory::createUser();
        UserFactory::addSystemRole(
            $interviewer,
            \OmegaUp\Authorization::INTERVIEWER_ROLE
        );

        // Verify I started with nothing
        $interviews = \OmegaUp\DAO\Interviews::getMyInterviews(
            $interviewer->user_id
        );
        $this->assertEquals(0, count($interviews));

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Interview::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'title' => 'My first interview',
            'alias' => 'my-first-interview',
            'duration' => 60,
        ]));

        $this->assertEquals('ok', $response['status']);

        $interviews = \OmegaUp\DAO\Interviews::getMyInterviews(
            $interviewer->user_id
        );

        // Must have 1 interview
        $this->assertEquals(1, count($interviews));
    }

    public function testInterviewsMustBePrivate() {
        ['user' => $interviewer, 'identity' => $identity] = UserFactory::createUser();
        UserFactory::addSystemRole(
            $interviewer,
            \OmegaUp\Authorization::INTERVIEWER_ROLE
        );

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Interview::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'title' => 'My second interview',
            'alias' => 'my-second-interview',
            'duration' => 60,
        ]));

        $interview = \OmegaUp\DAO\Interviews::getMyInterviews(
            $interviewer->user_id
        );
    }

    public function testAddUsersToInterview() {
        ['user' => $interviewer, 'identity' => $identity] = UserFactory::createUser();
        UserFactory::addSystemRole(
            $interviewer,
            \OmegaUp\Authorization::INTERVIEWER_ROLE
        );

        $login = self::login($identity);
        $interviewAlias = 'my-third-interview';
        $response = \OmegaUp\Controllers\Interview::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'title' => 'My third interview',
            'alias' => $interviewAlias,
            'duration' => 60,
        ]));

        $this->assertEquals('ok', $response['status']);

        // add 2 new users via email (not existing omegaupusers)
        $email1 = Utils::CreateRandomString() . 'a@foobar.net';
        $email2 = Utils::CreateRandomString() . 'b@foobar.net';

        $response = \OmegaUp\Controllers\Interview::apiAddUsers(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'interview_alias' => $interviewAlias,
            'usernameOrEmailsCSV' => $email1 . ',' . $email2,
        ]));
        $this->assertEquals('ok', $response['status']);

        $this->assertNotNull(
            $createdUser1 = \OmegaUp\DAO\Users::findByEmail(
                $email1
            ),
            'user should have been created by adding email to interview'
        );
        $this->assertNotNull(
            \OmegaUp\DAO\Users::findByEmail(
                $email2
            ),
            'user should have been created by adding email to interview'
        );

        $this->assertEquals(
            $createdUser1->verified,
            0,
            'new created users should not be email-validated'
        );

        // add 2 users that are already omegaup users (using registered email)
        $emailFor1 = Utils::CreateRandomString() . '@mail.com';
        ['user' => $interviewee1, 'identity' => $identity1] = UserFactory::createUser(
            new UserParams(
                ['email' => $emailFor1]
            )
        );

        $emailFor2 = Utils::CreateRandomString() . '@mail.com';
        ['user' => $interviewee2, 'identity' => $identity2] = UserFactory::createUser(
            new UserParams(
                ['email' => $emailFor2]
            )
        );

        $response = \OmegaUp\Controllers\Interview::apiAddUsers(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'interview_alias' => $interviewAlias,
            'usernameOrEmailsCSV' => $emailFor1 . ',' . $emailFor2,
        ]));
        $this->assertEquals('ok', $response['status']);

        // add 2 users that are already omegaup users (using registered username)
        ['user' => $interviewee3, 'identity' => $identity3] = UserFactory::createUser();
        ['user' => $interviewee4, 'identity' => $identity4] = UserFactory::createUser();

        $response = \OmegaUp\Controllers\Interview::apiAddUsers(new \OmegaUp\Request([
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
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testOnlyInterviewersCanCreateInterviews() {
        $r = new \OmegaUp\Request();

        // Create an interview
        ['user' => $interviewer, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Interview::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'title' => 'My fourth interview',
            'alias' => 'my-fourth-interview',
            'duration' => 60,
        ]));
    }
}
