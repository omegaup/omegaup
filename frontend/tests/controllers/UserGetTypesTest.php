<?php
/**
 * Tests function getUserTypes
 */
class UserGetTypesTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Get empty user types when user objectives are not yet established
     */
    public function testEmptyUserTypes() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        self::login($identity);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user,
            $identity
        );
        $this->assertEmpty($userTypes);
    }

    /**
     * Test only admin and owner can get user types
     */
    public function testOnlyAllowedUsersCanGetUserTypes() {
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identityUser2] = \OmegaUp\Test\Factories\User::createUser();
        self::login($identityUser2);

        try {
            \OmegaUp\Controllers\User::getUserTypes($user, $identityUser2);
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        //Admin can get user types
        ['identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();
        self::login(($identityAdmin));

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user,
            $identityAdmin
        );
        $this->assertEmpty($userTypes);
    }

    /**
     * A PHPUnit data provider for the test get correct user types
     *
     * @return list<array{0: bool, 1: bool, 2: bool, 3: bool, 4: list<string>}>
     * {has_competitive_objective, has_learning_objective, has_scholar_objective, has_teaching_objective, {user types}}
     */
    public function userObjectivesAndTypes(): array {
        return [
            // User types when learning objective is true
            [false, true, true, false, [\OmegaUp\Controllers\User::USER_TYPE_STUDENT]],
            [true, true, false, false, [\OmegaUp\Controllers\User::USER_TYPE_CONTESTANT]],
            [true, true, true, false, [\OmegaUp\Controllers\User::USER_TYPE_STUDENT, \OmegaUp\Controllers\User::USER_TYPE_CONTESTANT]],
            [false, true, false, false, [\OmegaUp\Controllers\User::USER_TYPE_SELF_TAUGHT]],

            // User types when teaching objective is true
            [false, false, true, true, [\OmegaUp\Controllers\User::USER_TYPE_TEACHER]],
            [true, false, false, true, [\OmegaUp\Controllers\User::USER_TYPE_COACH]],
            [true, false, true, true, [\OmegaUp\Controllers\User::USER_TYPE_TEACHER, \OmegaUp\Controllers\User::USER_TYPE_COACH]],
            [false, false, false, true, [\OmegaUp\Controllers\User::USER_TYPE_INDEPENDENT_TEACHER]],

            // User types when both learning and teaching objectives are true
            [false, true, true, true, [\OmegaUp\Controllers\User::USER_TYPE_STUDENT, \OmegaUp\Controllers\User::USER_TYPE_TEACHER]],
            [true, true, false, true, [\OmegaUp\Controllers\User::USER_TYPE_CONTESTANT, \OmegaUp\Controllers\User::USER_TYPE_COACH]],
            [true, true, true, true, [\OmegaUp\Controllers\User::USER_TYPE_STUDENT, \OmegaUp\Controllers\User::USER_TYPE_CONTESTANT,
                                        \OmegaUp\Controllers\User::USER_TYPE_TEACHER, \OmegaUp\Controllers\User::USER_TYPE_COACH]],
            [false, true, false, true, [\OmegaUp\Controllers\User::USER_TYPE_SELF_TAUGHT, \OmegaUp\Controllers\User::USER_TYPE_INDEPENDENT_TEACHER]],

            // 'curious' type when neither learning nor teaching objectives are true
            [false, false, true, false, [\OmegaUp\Controllers\User::USER_TYPE_CURIOUS]],
            [true, false, false, false, [\OmegaUp\Controllers\User::USER_TYPE_CURIOUS]],
            [true, false, true, false, [\OmegaUp\Controllers\User::USER_TYPE_CURIOUS]],
            [false, false, false, false, [\OmegaUp\Controllers\User::USER_TYPE_CURIOUS]],
        ];
    }

    /**
     * @dataProvider userObjectivesAndTypes
     */
    public function testCorrectGetUserTypes(
        bool $has_competitive_objective,
        bool $has_learning_objective,
        bool $has_scholar_objective,
        bool $has_teaching_objective,
        array $expectedUserTypes
    ) {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => $has_competitive_objective,
            'has_learning_objective' => $has_learning_objective,
            'has_scholar_objective' => $has_scholar_objective,
            'has_teaching_objective' => $has_teaching_objective,
        ]));

        $user = \OmegaUp\DAO\Users::getByPK($identity->user_id);
        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user,
            $identity
        );
        $this->assertEqualsCanonicalizing($expectedUserTypes, $userTypes);
    }
}
