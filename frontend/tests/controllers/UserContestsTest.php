<?php

/**
 * Description of UserContestsTest
 */
class UserContestsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Get contests where user is the director
     */
    public function testDirectorList() {
        // Our director
        ['user' => $director, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $contestData[0] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['contestDirector' => $identity]
            )
        );
        $contestData[1] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['contestDirector' => $identity]
            )
        );

        // Call api
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Contest::apiMyList($r);

        // Contests should come ordered by contest id desc
        $this->assertEquals(count($contestData), count($response['contests']));
        $this->assertEquals(
            $contestData[1]['request']['alias'],
            $response['contests'][0]['alias']
        );
        $this->assertEquals(
            $contestData[0]['request']['alias'],
            $response['contests'][1]['alias']
        );
    }

    /**
     * Test getting list of contests where the user is the admin
     */
    public function testAdminList() {
        // Our director
        ['user' => $director, 'identity' => $directorIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $contestAdminData = [];

        // Create a group with two arbitrary users.
        $helperGroup = \OmegaUp\Test\Factories\Groups::createGroup(
            $directorIdentity
        );
        $users = [];
        $identities = [];
        for ($i = 0; $i < 2; $i++) {
            ['user' => $users[$i], 'identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Groups::addUserToGroup(
                $helperGroup,
                $identities[$i]
            );
        }

        // Get two contests with another director, add $director to their
        // admin list
        $contestAdminData[0] = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestAdminData[0],
            $directorIdentity
        );
        \OmegaUp\Test\Factories\Contest::addGroupAdmin(
            $contestAdminData[0],
            $helperGroup['group']
        );

        // Get two contests with another director, add $director to their
        // group admin list
        $contestAdminData[1] = \OmegaUp\Test\Factories\Contest::createContest();
        $group = \OmegaUp\Test\Factories\Groups::createGroup(
            $contestAdminData[1]['director']
        );
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $group,
            $directorIdentity
        );
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup($group, $identity);
        \OmegaUp\Test\Factories\Contest::addGroupAdmin(
            $contestAdminData[1],
            $group['group']
        );
        \OmegaUp\Test\Factories\Contest::addGroupAdmin(
            $contestAdminData[1],
            $helperGroup['group']
        );

        $contestDirectorData[0] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['contestDirector' => $directorIdentity]
            )
        );
        \OmegaUp\Test\Factories\Contest::addGroupAdmin(
            $contestDirectorData[0],
            $helperGroup['group']
        );
        $contestDirectorData[1] = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                [
                    'contestDirector' => $directorIdentity,
                    'admissionMode' => 'private',
                ]
            )
        );
        \OmegaUp\Test\Factories\Contest::addGroupAdmin(
            $contestDirectorData[1],
            $helperGroup['group']
        );

        // Call api
        $login = self::login($directorIdentity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Contest::apiAdminList($r);

        // Contests should come ordered by contest id desc
        $this->assertEquals(
            count(
                $contestDirectorData
            ) + count(
                $contestAdminData
            ),
            count(
                $response['contests']
            )
        );
        $this->assertEquals(
            $contestDirectorData[1]['request']['alias'],
            $response['contests'][0]['alias']
        );
        $this->assertEquals(
            $contestDirectorData[0]['request']['alias'],
            $response['contests'][1]['alias']
        );
        $this->assertEquals(
            $contestAdminData[1]['request']['alias'],
            $response['contests'][2]['alias']
        );
        $this->assertEquals(
            $contestAdminData[0]['request']['alias'],
            $response['contests'][3]['alias']
        );
    }

    /**
     * Test \OmegaUp\DAO\Contests::getPrivateContestsCount when there's 1 private contest
     * count
     */
    public function testPrivateContestsCount() {
        // Create private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        $user = $contestData['userDirector'];

        $this->assertEquals(
            1,
            \OmegaUp\DAO\Contests::getPrivateContestsCount(
                $user
            )
        );
    }

    /**
     * Test \OmegaUp\DAO\Contests::getPrivateContestsCount when there's 1 public contest
     */
    public function testPrivateContestsCountWithPublicContest() {
        // Create private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $user = $contestData['userDirector'];

        $this->assertEquals(
            0,
            \OmegaUp\DAO\Contests::getPrivateContestsCount(
                $user
            )
        );
    }

    /**
     * Test \OmegaUp\DAO\Contests::getPrivateContestsCount when there's 0 contests
     * created
     */
    public function testPrivateContestsCountWithNoContests() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $this->assertEquals(
            0,
            \OmegaUp\DAO\Contests::getPrivateContestsCount(
                $user
            )
        );
    }
}
