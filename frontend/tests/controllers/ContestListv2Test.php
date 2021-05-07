<?php

/**
 * Description of Contest List v2
 *
 * @author Michael Serrato
 */

class ContestListv2Test extends \OmegaUp\Test\ControllerTestCase {
    public function testPublicCurrentContestList() {
        $secondsDay = 24 * 60  * 60;
        $now = time();
        $yesterday = $now - $secondsDay;
        $tomorrow = $now + $secondsDay;
        $contests = [];

        // Create public contests
        for ($i = 0; $i < 2; ++$i) {
            $contests[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'public',
                    'startTime' => new \OmegaUp\Timestamp($yesterday),
                    'finishTime' => new \OmegaUp\Timestamp($tomorrow),
                    'requestsUserInformation' => 'optional',
                ])
            )['contest'];
        }

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = array_map(
            fn ($contest) => $contest['alias'],
            $contestListPayload['contests']['current']
        );
        $contestAliases = array_map(
            fn ($contest) => $contest->alias,
            $contests
        );

        sort($contestListPayloadAliases);
        sort($contestAliases);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
        );
    }

    public function testPrivateCurrentContestList() {
        $secondsDay = 24 * 60  * 60;
        $now = time();
        $yesterday = $now - $secondsDay;
        $tomorrow = $now + $secondsDay;
        $contests = [];

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create public contests
        for ($i = 0; $i < 2; ++$i) {
            $contests[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'public',
                    'startTime' => new \OmegaUp\Timestamp($yesterday),
                    'finishTime' => new \OmegaUp\Timestamp($tomorrow),
                    'requestsUserInformation' => 'optional',
                ])
            )['contest'];
        }

        // Create private contests
        for ($i = 0; $i < 2; ++$i) {
            $contestData = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'private',
                    'startTime' => new \OmegaUp\Timestamp($yesterday),
                    'finishTime' => new \OmegaUp\Timestamp($tomorrow),
                    'requestsUserInformation' => 'optional',
                ])
            );

            // Add user to our contest
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $identity
            );

            $contests[] = $contestData['contest'];
        }

        $userLogin = self::login($identity);
        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = array_map(
            fn ($contest) => $contest['alias'],
            $contestListPayload['contests']['current']
        );
        $contestAliases = array_map(
            fn ($contest) => $contest->alias,
            $contests
        );

        sort($contestListPayloadAliases);
        sort($contestAliases);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
        );
    }

    public function testPublicFutureContestList() {
        $secondsDay = 24 * 60  * 60;
        $now = time();
        $tomorrow = $now + $secondsDay;
        $afterTomorrow = $tomorrow + $secondsDay;
        $contests = [];

        // Create public contests
        for ($i = 0; $i < 2; ++$i) {
            $contests[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'public',
                    'startTime' => new \OmegaUp\Timestamp($tomorrow),
                    'finishTime' => new \OmegaUp\Timestamp($afterTomorrow),
                    'requestsUserInformation' => 'optional',
                ])
            )['contest'];
        }

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = array_map(
            fn ($contest) => $contest['alias'],
            $contestListPayload['contests']['future']
        );
        $contestAliases = array_map(
            fn ($contest) => $contest->alias,
            $contests
        );

        sort($contestListPayloadAliases);
        sort($contestAliases);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
        );
    }

    public function testPrivateFutureContestList() {
        $secondsDay = 24 * 60  * 60;
        $now = time();
        $tomorrow = $now + $secondsDay;
        $afterTomorrow = $tomorrow + $secondsDay;
        $contests = [];

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create public contests
        for ($i = 0; $i < 2; ++$i) {
            $contests[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'public',
                    'startTime' => new \OmegaUp\Timestamp($tomorrow),
                    'finishTime' => new \OmegaUp\Timestamp($afterTomorrow),
                    'requestsUserInformation' => 'optional',
                ])
            )['contest'];
        }

        // Create private contests
        for ($i = 0; $i < 2; ++$i) {
            $contestData = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'private',
                    'startTime' => new \OmegaUp\Timestamp($tomorrow),
                    'finishTime' => new \OmegaUp\Timestamp($afterTomorrow),
                    'requestsUserInformation' => 'optional',
                ])
            );

            // Add user to our contest
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $identity
            );

            $contests[] = $contestData['contest'];
        }

        $userLogin = self::login($identity);
        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = array_map(
            fn ($contest) => $contest['alias'],
            $contestListPayload['contests']['future']
        );
        $contestAliases = array_map(
            fn ($contest) => $contest->alias,
            $contests
        );

        sort($contestListPayloadAliases);
        sort($contestAliases);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
        );
    }

    public function testPublicPastContestList() {
        $secondsDay = 24 * 60  * 60;
        $now = time();
        $yesterday = $now - $secondsDay;
        $beforeYesterday = $yesterday - $secondsDay;
        $contests = [];

         // Create public contests
        for ($i = 0; $i < 2; ++$i) {
            $contests[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'public',
                    'startTime' => new \OmegaUp\Timestamp($beforeYesterday),
                    'finishTime' => new \OmegaUp\Timestamp($yesterday),
                    'requestsUserInformation' => 'optional',
                ])
            )['contest'];
        }

        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request()
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = array_map(
            fn ($contest) => $contest['alias'],
            $contestListPayload['contests']['past']
        );
        $contestAliases = array_map(
            fn ($contest) => $contest->alias,
            $contests
        );

        sort($contestListPayloadAliases);
        sort($contestAliases);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
        );
    }

    public function testPrivatePastContestList() {
        $secondsDay = 24 * 60  * 60;
        $now = time();
        $yesterday = $now - $secondsDay;
        $beforeYesterday = $yesterday - $secondsDay;
        $contests = [];

        // Create user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

         // Create public contests
        for ($i = 0; $i < 2; ++$i) {
            $contests[] = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                    'admissionMode' => 'public',
                    'startTime' => new \OmegaUp\Timestamp($beforeYesterday),
                    'finishTime' => new \OmegaUp\Timestamp($yesterday),
                    'requestsUserInformation' => 'optional',
                ])
            )['contest'];
        }

         // Create private contests
        for ($i = 0; $i < 2; ++$i) {
            $contestData = \OmegaUp\Test\Factories\Contest::createContest(
                new \OmegaUp\Test\Factories\ContestParams([
                   'admissionMode' => 'private',
                   'startTime' => new \OmegaUp\Timestamp($beforeYesterday),
                   'finishTime' => new \OmegaUp\Timestamp($yesterday),
                   'requestsUserInformation' => 'optional',
                ])
            );

           // Add user to our contest
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $identity
            );

            $contests[] = $contestData['contest'];
        }

        $userLogin = self::login($identity);
        $contestListPayload = \OmegaUp\Controllers\Contest::getContestListDetailsv2ForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
            ])
        )['smartyProperties']['payload'];

        $contestListPayloadAliases = array_map(
            fn ($contest) => $contest['alias'],
            $contestListPayload['contests']['past']
        );
        $contestAliases = array_map(
            fn ($contest) => $contest->alias,
            $contests
        );

        sort($contestListPayloadAliases);
        sort($contestAliases);

        $this->assertEquals(
            $contestListPayloadAliases,
            $contestAliases
        );
    }
}
