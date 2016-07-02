<?php

/**
 *
 * @author alanboy
 */

class InterviewCreateTest extends OmegaupTestCase {
    public function testCreateAngListInterview() {
        $r = new Request();

        $contestant = UserFactory::createUser();

        // Verify I started with nothing
        $interviews = ContestsDAO::getMyInterviews($contestant->user_id);
        $this->assertEquals(0, count($interviews));

        $r['auth_token'] = $this->login($contestant);
        $r['title'] = 'My second interview';
        $r['alias'] = 'my-first-interview';
        $r['duration'] = 60;

        $response = InterviewController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);

        $interviews = ContestsDAO::getMyInterviews($contestant->user_id);

        // Must have 1 interview
        $this->assertEquals(1, count($interviews));
    }

    public function testInterviewsMustBePrivateAndRegistrationRequired() {
        $r = new Request();

        $contestant = UserFactory::createUser();

        $r['auth_token'] = $this->login($contestant);
        $r['title'] = 'My second interview';
        $r['alias'] = 'my-second-interview';
        $r['duration'] = 60;

        $response = InterviewController::apiCreate($r);

        $interview = ContestsDAO::getMyInterviews($contestant->user_id);

        $this->assertEquals(1, $interview[0]['interview']);
        $this->assertEquals(0, $interview[0]['public']);
        $this->assertEquals(1, $interview[0]['contestant_must_register'], 'Interviews must have the contestant_must_register property');

        // you CAN'T change those properties from an interview,
    }

    //public function testInterviewsListing() {
    //    //public static function apiList(Request $r) {
    //}
}
