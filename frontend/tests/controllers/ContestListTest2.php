<?php

/**
 * Description of Contest List v2
 *
 * @author Michael Serrato
 */

class ContestListTest2 extends \OmegaUp\Test\ControllerTestCase {
    public function testContestListDataForTypescript() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'requestsUserInformation' => 'optional',
            ])
        );

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our contest
        \OmegaUp\Test\Factories\Contest::addUser(
            $contestData,
            $identity
        );

        // Log in user
        $userLogin = self::login($identity);

        $contestListDetails = \OmegaUp\Controllers\Contest::getContestListDetailsForTypeScript2(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
            ])
        )['smartyProperties']['payload'];

        $this->assertArrayHasKey('isLogged', $contestListDetails);
        $this->assertIsBool($contestListDetails['isLogged']);

        $this->assertArrayHasKey('contests', $contestListDetails);

        $this->assertArrayHasKey('current', $contestListDetails['contests']);
        $this->assertIsArray($contestListDetails['contests']['current']);

        $this->assertArrayHasKey('future', $contestListDetails['contests']);
        $this->assertIsArray($contestListDetails['contests']['future']);

        $this->assertArrayHasKey('past', $contestListDetails['contests']);
        $this->assertIsArray($contestListDetails['contests']['past']);
    }

    public function testGetContestList2() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'title' => 'test-contest-1'
            ])
        );

        $contests = \OmegaUp\Controllers\Contest::getContestList2();

        $this->assertArrayHasKey('title', $contests[0]);
    }
}
