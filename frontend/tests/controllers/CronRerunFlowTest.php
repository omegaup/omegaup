<?php

/**
 * Runs a manual cron rerun from the api request to its final status.
 */
class CronRerunFlowTest extends \OmegaUp\Test\ControllerTestCase {
    public function testRerunGoesFromTheApiToADoneRequest() {
        [
            'identity' => $identity,
            'user' => $user,
        ] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        $name = \OmegaUp\CronJobName::AggregateFeedback->value;

        $response = \OmegaUp\Controllers\Admin::apiRerunCron(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
            ])
        );
        $this->assertTrue($response['queued']);
        $request = \OmegaUp\DAO\CronRunRequests::getActiveByName($name);
        $this->assertNotNull($request);
        $this->assertSame(
            \OmegaUp\CronRunRequestStatus::Pending->value,
            $request->status
        );

        \OmegaUp\Test\Utils::runCronDispatcher();

        $request = \OmegaUp\DAO\CronRunRequests::getByPK(
            intval($request->request_id)
        );
        $this->assertNotNull($request);
        $this->assertSame(
            \OmegaUp\CronRunRequestStatus::Done->value,
            $request->status,
            strval($request->error_text)
        );
        $this->assertNotNull($request->picked_at);
        $this->assertNotNull($request->finished_at);
        $this->assertNull(\OmegaUp\DAO\CronRunRequests::getActiveByName($name));

        $run = \OmegaUp\DAO\CronRuns::getByPK(intval($request->run_id));
        $this->assertNotNull($run);
        $this->assertSame($name, $run->name);
        $this->assertSame(\OmegaUp\CronRunStatus::Success->value, $run->status);

        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $user
        );
        $this->assertCount(1, $notifications);
        $contents = json_decode($notifications[0]['contents'], true);
        $this->assertSame('cron-rerun', $contents['type']);
        $this->assertSame(
            'notificationCronRerunSucceeded',
            $contents['body']['localizationString']
        );
    }
}
