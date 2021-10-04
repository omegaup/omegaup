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
        $login = self::login($identity);

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertEmpty($userTypes);
    }

    /**
     * Test only admin and owner can get user types
     */
    public function testOnlyAllowedUsersCanGetUserTypes() {
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity2);

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        try {
            \OmegaUp\Controllers\User::getUserTypes($identity1->user_id, $r);
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        //Admin can get user types
        ['identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login(($identityAdmin));
        $r = new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $identity1->user_id,
            $r
        );
        $this->assertEmpty($userTypes);
    }

    /**
     * Get user types when learning objective is true
     */
    public function testLearningObjectiveUserTypes() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        //should only get 'student' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => true,
            'has_scholar_objective' => true,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'contestant' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => true,
            'has_scholar_objective' => false,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'contestant' and 'student' types
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => true,
            'has_scholar_objective' => true,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertContains('student', $userTypes);
        $this->assertContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'self-taught' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => true,
            'has_scholar_objective' => false,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);
    }

    /**
     * Get user types when teaching objective is true
     */
    public function testTeachingObjectiveUserTypes() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        //should only get 'teacher' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => false,
            'has_scholar_objective' => true,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'coach' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => false,
            'has_scholar_objective' => false,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'teacher' and 'coach' types
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => false,
            'has_scholar_objective' => true,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertContains('teacher', $userTypes);
        $this->assertContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'independent-teacher' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => false,
            'has_scholar_objective' => false,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);
    }

    /**
     * Get user types when learning and teaching objectives are true
     */
    public function testLearningAndTeachingObjectivesUserTypes() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        //should only get 'student' and 'teacher' types
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => true,
            'has_scholar_objective' => true,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'contestant' and 'coach' types
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => true,
            'has_scholar_objective' => false,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'student', 'contestant', 'teacher' and 'coach' types
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => true,
            'has_scholar_objective' => true,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertContains('student', $userTypes);
        $this->assertContains('contestant', $userTypes);
        $this->assertContains('teacher', $userTypes);
        $this->assertContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);

        //should only get 'self-taught' and 'independent-teacher' types
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => true,
            'has_scholar_objective' => false,
            'has_teaching_objective' => true,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertContains('self-taught', $userTypes);
        $this->assertContains('independent-teacher', $userTypes);
        $this->assertNotContains('curious', $userTypes);
    }

    /**
     * Get user types when neither learning nor teaching objectives are true
     */
    public function testCuriousUserType() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        //should only get 'curious' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => false,
            'has_scholar_objective' => true,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertContains('curious', $userTypes);

        //should only get 'curious' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => false,
            'has_scholar_objective' => false,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertContains('curious', $userTypes);

        //should only get 'curious' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => true,
            'has_learning_objective' => false,
            'has_scholar_objective' => true,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertContains('curious', $userTypes);

        //should only get 'curious' type
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'has_competitive_objective' => false,
            'has_learning_objective' => false,
            'has_scholar_objective' => false,
            'has_teaching_objective' => false,
        ]);
        \OmegaUp\Controllers\User::apiUpdate($r);

        $userTypes = \OmegaUp\Controllers\User::getUserTypes(
            $user->user_id,
            $r
        );
        $this->assertNotContains('student', $userTypes);
        $this->assertNotContains('contestant', $userTypes);
        $this->assertNotContains('teacher', $userTypes);
        $this->assertNotContains('coach', $userTypes);
        $this->assertNotContains('self-taught', $userTypes);
        $this->assertNotContains('independent-teacher', $userTypes);
        $this->assertContains('curious', $userTypes);
    }
}
