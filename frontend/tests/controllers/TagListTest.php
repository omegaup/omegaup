<?php

class TagListTest extends \OmegaUp\Test\ControllerTestCase {
    public function testValidParameter(): array {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Tag::apiFrequentTags(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemLevel' => 'problemLevelBasicIntroductionToProgramming'
            ]));
            $this->fail('Incorrect parameter');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalid', $e->getMessage());
        }
    }

    public function testGetListOfFrequentTags() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Tag::apiFrequentTags(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemLevel' => 'problemLevelBasicIntroductionToProgramming'
            ])
        );
        $this->assertIsArray($response);
    }
}
